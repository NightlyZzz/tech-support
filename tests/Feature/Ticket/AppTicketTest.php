<?php

namespace Tests\Feature\Ticket;

use App\Enums\Role\RoleType;
use App\Enums\Ticket\TicketStatusType;
use App\Enums\Ticket\TicketTypeEnum;
use App\Models\Ticket\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_app_ticket_endpoints(): void
    {
        $response = $this->getJson('/api/app/ticket/my');

        $response->assertUnauthorized();
    }

    public function test_default_user_can_create_ticket_via_app(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user, 'iphone_create_ticket')
            ->postJson('/api/app/ticket', [
                'description' => 'Не работает приложение на телефоне',
                'contact_phone' => '+79990001122',
                'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Заявка успешно создана',
            ])
            ->assertJsonPath('data.sender_id', $user->id)
            ->assertJsonPath('data.contact_phone', '79990001122')
            ->assertJsonPath('data.status_id', TicketStatusType::Pending->value);

        $this->assertDatabaseHas('tickets', [
            'sender_id' => $user->id,
            'description' => 'Не работает приложение на телефоне',
            'contact_phone' => '79990001122',
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);
    }

    public function test_user_sees_only_own_tickets_in_app_my_list(): void
    {
        $firstUser = User::factory()->withRole(RoleType::User)->create();
        $secondUser = User::factory()->withRole(RoleType::User)->create();

        Ticket::query()->create([
            'sender_id' => $firstUser->id,
            'description' => 'Мой тикет в приложении',
            'contact_phone' => '79990000010',
            'ticket_type_id' => TicketTypeEnum::SetupSoftware->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        Ticket::query()->create([
            'sender_id' => $secondUser->id,
            'description' => 'Чужой тикет в приложении',
            'contact_phone' => '79990000011',
            'ticket_type_id' => TicketTypeEnum::SupportSreda->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        $response = $this
            ->withAuthToken($firstUser, 'iphone_my_tickets')
            ->getJson('/api/app/ticket/my');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'description' => 'Мой тикет в приложении',
            ])
            ->assertJsonMissing([
                'description' => 'Чужой тикет в приложении',
            ]);
    }

    public function test_employee_sees_only_unassigned_tickets_in_app_all_list(): void
    {
        $employee = User::factory()->withRole(RoleType::Employee)->create();
        $sender = User::factory()->withRole(RoleType::User)->create();
        $anotherEmployee = User::factory()->withRole(RoleType::Employee)->create();

        Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Свободная app заявка',
            'contact_phone' => '79990000012',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
            'employee_id' => null,
        ]);

        Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Занятая app заявка',
            'contact_phone' => '79990000013',
            'ticket_type_id' => TicketTypeEnum::InstallSoftware->value,
            'ticket_status_id' => TicketStatusType::Review->value,
            'employee_id' => $anotherEmployee->id,
        ]);

        $response = $this
            ->withAuthToken($employee, 'macbook_all_tickets')
            ->getJson('/api/app/ticket/all');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'description' => 'Свободная app заявка',
            ])
            ->assertJsonMissing([
                'description' => 'Занятая app заявка',
            ]);
    }

    public function test_admin_sees_all_tickets_in_app_all_list(): void
    {
        $admin = User::factory()->withRole(RoleType::Admin)->create();
        $sender = User::factory()->withRole(RoleType::User)->create();
        $employee = User::factory()->withRole(RoleType::Employee)->create();

        Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Свободный тикет для админа',
            'contact_phone' => '79990000014',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
            'employee_id' => null,
        ]);

        Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Назначенный тикет для админа',
            'contact_phone' => '79990000015',
            'ticket_type_id' => TicketTypeEnum::InstallSoftware->value,
            'ticket_status_id' => TicketStatusType::Review->value,
            'employee_id' => $employee->id,
        ]);

        $response = $this
            ->withAuthToken($admin, 'ipad_admin_tickets')
            ->getJson('/api/app/ticket/all');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'description' => 'Свободный тикет для админа',
            ])
            ->assertJsonFragment([
                'description' => 'Назначенный тикет для админа',
            ]);
    }

    public function test_employee_can_take_unassigned_ticket_into_work_via_app(): void
    {
        $employee = User::factory()->withRole(RoleType::Employee)->create();
        $sender = User::factory()->withRole(RoleType::User)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'App новая заявка',
            'contact_phone' => '79990000016',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
            'employee_id' => null,
        ]);

        $response = $this
            ->withAuthToken($employee, 'iphone_employee_update')
            ->putJson('/api/app/ticket/' . $ticket->id, [
                'employee_id' => $employee->id,
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Данные заявки были успешно обновлены',
            ])
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.status_id', TicketStatusType::Review->value);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'employee_id' => $employee->id,
            'ticket_status_id' => TicketStatusType::Review->value,
        ]);
    }

    public function test_sender_can_attach_log_to_own_ticket_via_app(): void
    {
        $sender = User::factory()->withRole(RoleType::User)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'App заявка с сообщением',
            'contact_phone' => '79990000017',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        $response = $this
            ->withAuthToken($sender, 'iphone_sender_log')
            ->postJson('/api/app/ticket/log/' . $ticket->id, [
                'message' => 'Пишу из приложения',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.ticket_id', $ticket->id)
            ->assertJsonPath('data.sender_id', $sender->id)
            ->assertJsonPath('data.message', 'Пишу из приложения')
            ->assertJsonPath('data.is_employee_message', false);

        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'sender_id' => $sender->id,
            'message' => 'Пишу из приложения',
        ]);
    }

    public function test_app_ticket_statuses_endpoint_returns_data(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user, 'iphone_statuses')
            ->getJson('/api/app/ticket/status/all');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => TicketStatusType::Pending->value,
            ])
            ->assertJsonFragment([
                'id' => TicketStatusType::Review->value,
            ]);
    }

    public function test_app_ticket_types_endpoint_returns_data(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user, 'iphone_types')
            ->getJson('/api/app/ticket/type/all');

        $response
            ->assertOk()
            ->assertJsonFragment([
                'id' => TicketTypeEnum::SetupSoftware->value,
            ])
            ->assertJsonFragment([
                'id' => TicketTypeEnum::SupportEdu->value,
            ]);
    }
}
