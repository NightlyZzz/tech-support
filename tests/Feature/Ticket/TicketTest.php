<?php

namespace Tests\Feature\Ticket;

use App\Enums\Role\RoleType;
use App\Enums\Ticket\TicketStatusType;
use App\Enums\Ticket\TicketTypeEnum;
use App\Models\Ticket\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_ticket_endpoints(): void
    {
        $response = $this->getJson('/api/ticket/my');

        $response->assertUnauthorized();
    }

    public function test_default_user_can_create_ticket(): void
    {
        $user = User::factory()->withRole(RoleType::User)->create();

        $response = $this
            ->withAuthToken($user)
            ->postJson('/api/ticket', [
                'description' => 'Не работает корпоративная почта',
                'contact_phone' => '+79991234567',
                'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Заявка успешно создана',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'description',
                    'contact_phone',
                    'sender_id',
                    'employee_id',
                    'type_id',
                    'type_name',
                    'status_id',
                    'status_name',
                    'created_at',
                    'sender_name',
                    'employee_name',
                ],
            ]);

        $this->assertDatabaseHas('tickets', [
            'sender_id' => $user->id,
            'description' => 'Не работает корпоративная почта',
            'contact_phone' => '79991234567',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);
    }

    public function test_employee_cannot_create_ticket(): void
    {
        $employee = User::factory()->withRole(RoleType::Employee)->create();

        $response = $this
            ->withAuthToken($employee)
            ->postJson('/api/ticket', [
                'description' => 'Нужно установить программу',
                'contact_phone' => '+79991234567',
                'ticket_type_id' => TicketTypeEnum::InstallSoftware->value,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_user_sees_only_own_tickets_in_my_list(): void
    {
        $firstUser = User::factory()->withRole(RoleType::User)->create();
        $secondUser = User::factory()->withRole(RoleType::User)->create();

        Ticket::query()->create([
            'sender_id' => $firstUser->id,
            'description' => 'Заявка первого пользователя',
            'contact_phone' => '79990000001',
            'ticket_type_id' => TicketTypeEnum::SetupSoftware->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        Ticket::query()->create([
            'sender_id' => $secondUser->id,
            'description' => 'Заявка второго пользователя',
            'contact_phone' => '79990000002',
            'ticket_type_id' => TicketTypeEnum::SupportSreda->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        $response = $this
            ->withAuthToken($firstUser)
            ->getJson('/api/ticket/my');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'description' => 'Заявка первого пользователя',
            ])
            ->assertJsonMissing([
                'description' => 'Заявка второго пользователя',
            ]);
    }

    public function test_user_cannot_view_foreign_ticket(): void
    {
        $owner = User::factory()->withRole(RoleType::User)->create();
        $anotherUser = User::factory()->withRole(RoleType::User)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $owner->id,
            'description' => 'Чужая заявка',
            'contact_phone' => '79990000003',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        $response = $this
            ->withAuthToken($anotherUser)
            ->getJson('/api/ticket/' . $ticket->id);

        $response->assertForbidden();
    }

    public function test_employee_sees_only_unassigned_tickets_in_all_list(): void
    {
        $employee = User::factory()->withRole(RoleType::Employee)->create();
        $sender = User::factory()->withRole(RoleType::User)->create();
        $anotherEmployee = User::factory()->withRole(RoleType::Employee)->create();

        Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Свободная заявка',
            'contact_phone' => '79990000004',
            'ticket_type_id' => TicketTypeEnum::SetupSoftware->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
            'employee_id' => null,
        ]);

        Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Уже занятая заявка',
            'contact_phone' => '79990000005',
            'ticket_type_id' => TicketTypeEnum::InstallSoftware->value,
            'ticket_status_id' => TicketStatusType::Review->value,
            'employee_id' => $anotherEmployee->id,
        ]);

        $response = $this
            ->withAuthToken($employee)
            ->getJson('/api/ticket/all');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'description' => 'Свободная заявка',
            ])
            ->assertJsonMissing([
                'description' => 'Уже занятая заявка',
            ]);
    }

    public function test_employee_can_take_unassigned_ticket_into_work(): void
    {
        $employee = User::factory()->withRole(RoleType::Employee)->create();
        $sender = User::factory()->withRole(RoleType::User)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Новая заявка',
            'contact_phone' => '79990000006',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
            'employee_id' => null,
        ]);

        $response = $this
            ->withAuthToken($employee)
            ->putJson('/api/ticket/' . $ticket->id, [
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

    public function test_sender_can_attach_log_to_own_ticket(): void
    {
        $sender = User::factory()->withRole(RoleType::User)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $sender->id,
            'description' => 'Заявка с сообщением',
            'contact_phone' => '79990000007',
            'ticket_type_id' => TicketTypeEnum::SupportEdu->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        $response = $this
            ->withAuthToken($sender)
            ->postJson('/api/ticket/log/' . $ticket->id, [
                'message' => 'Проблема все еще актуальна',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.ticket_id', $ticket->id)
            ->assertJsonPath('data.sender_id', $sender->id)
            ->assertJsonPath('data.message', 'Проблема все еще актуальна')
            ->assertJsonPath('data.is_employee_message', false);

        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'sender_id' => $sender->id,
            'message' => 'Проблема все еще актуальна',
        ]);
    }

    public function test_employee_can_attach_log_to_assigned_ticket(): void
    {
        $sender = User::factory()->withRole(RoleType::User)->create();
        $employee = User::factory()->withRole(RoleType::Employee)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $sender->id,
            'employee_id' => $employee->id,
            'description' => 'Заявка сотрудника',
            'contact_phone' => '79990000008',
            'ticket_type_id' => TicketTypeEnum::InstallSoftware->value,
            'ticket_status_id' => TicketStatusType::Review->value,
        ]);

        $response = $this
            ->withAuthToken($employee)
            ->postJson('/api/ticket/log/' . $ticket->id, [
                'message' => 'Начал разбирать проблему',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.ticket_id', $ticket->id)
            ->assertJsonPath('data.employee_id', $employee->id)
            ->assertJsonPath('data.message', 'Начал разбирать проблему')
            ->assertJsonPath('data.is_employee_message', true);

        $this->assertDatabaseHas('ticket_logs', [
            'ticket_id' => $ticket->id,
            'employee_id' => $employee->id,
            'message' => 'Начал разбирать проблему',
        ]);
    }

    public function test_user_cannot_attach_log_to_foreign_ticket(): void
    {
        $owner = User::factory()->withRole(RoleType::User)->create();
        $anotherUser = User::factory()->withRole(RoleType::User)->create();

        $ticket = Ticket::query()->create([
            'sender_id' => $owner->id,
            'description' => 'Закрытый доступ к логу',
            'contact_phone' => '79990000009',
            'ticket_type_id' => TicketTypeEnum::SupportSreda->value,
            'ticket_status_id' => TicketStatusType::Pending->value,
        ]);

        $response = $this
            ->withAuthToken($anotherUser)
            ->postJson('/api/ticket/log/' . $ticket->id, [
                'message' => 'Пытаюсь написать в чужую заявку',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('ticket_logs', 0);
    }
}
