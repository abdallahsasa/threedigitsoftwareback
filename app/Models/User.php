<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? 'admin') === 'admin';
    }

    public function hasErpPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $perms = $this->permissions ?? [];
        return in_array($permission, $perms, true);
    }

    public function canViewAccounting(): bool
    {
        return $this->hasErpPermission('view_accounting');
    }

    public function canManageClientsAndProjects(): bool
    {
        return $this->hasErpPermission('manage_clients_and_projects');
    }

    public function canCreateDrafts(): bool
    {
        return $this->hasErpPermission('create_drafts');
    }

    public function canIssueInvoices(): bool
    {
        return $this->hasErpPermission('issue_invoices');
    }

    public function canRecordPaymentsAndExpenses(): bool
    {
        return $this->hasErpPermission('record_payments_and_expenses');
    }

    public function canSendDocuments(): bool
    {
        return $this->hasErpPermission('send_documents');
    }

    public function canImportHistoricalData(): bool
    {
        return $this->hasErpPermission('import_historical_data');
    }

    public function canApproveReconciliation(): bool
    {
        return $this->hasErpPermission('approve_reconciliation');
    }

    public function canManageCompanySettings(): bool
    {
        return $this->hasErpPermission('manage_company_settings');
    }
}
