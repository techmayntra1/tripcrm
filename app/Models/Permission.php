<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'module',
        'action',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions')->withTimestamps();
    }

    public static function getModules(): array
    {
        return [
            'leads' => ['name' => 'Leads', 'icon' => 'bi-person-lines-fill', 'color' => 'bg-info'],
            'customers' => ['name' => 'Customers', 'icon' => 'bi-people-fill', 'color' => 'bg-success'],
            'vendors' => ['name' => 'Vendors', 'icon' => 'bi-shop', 'color' => 'bg-warning'],
            'meetings' => ['name' => 'Meetings', 'icon' => 'bi-calendar-event', 'color' => 'bg-purple'],
            'tasks' => ['name' => 'Tasks', 'icon' => 'bi-list-task', 'color' => 'bg-warning'],
            'projects' => ['name' => 'Projects', 'icon' => 'bi-kanban', 'color' => 'bg-primary'],
            'quotations' => ['name' => 'Quotations', 'icon' => 'bi-file-earmark-text', 'color' => 'bg-info'],
            'invoices' => ['name' => 'Invoices', 'icon' => 'bi-receipt', 'color' => 'bg-success'],
            'income' => ['name' => 'Income', 'icon' => 'bi-cash-stack', 'color' => 'bg-success'],
            'expenses' => ['name' => 'Expenses', 'icon' => 'bi-cash-coin', 'color' => 'bg-danger'],
            'staff' => ['name' => 'Staff & Salary', 'icon' => 'bi-person-badge', 'color' => 'bg-info'],
            'banks' => ['name' => 'Banks', 'icon' => 'bi-bank', 'color' => 'bg-primary'],
            'users' => ['name' => 'Users', 'icon' => 'bi-person-gear', 'color' => 'bg-dark'],
            'roles' => ['name' => 'Roles', 'icon' => 'bi-shield-lock', 'color' => 'bg-danger'],
            'masters' => ['name' => 'Masters', 'icon' => 'bi-gear', 'color' => 'bg-secondary'],
        ];
    }

    public static function getActions(): array
    {
        return [
            'view' => ['name' => 'View', 'icon' => 'bi-eye', 'color' => 'bg-info'],
            'create' => ['name' => 'Create', 'icon' => 'bi-plus-lg', 'color' => 'bg-success'],
            'edit' => ['name' => 'Edit', 'icon' => 'bi-pencil', 'color' => 'bg-warning'],
            'delete' => ['name' => 'Delete', 'icon' => 'bi-trash', 'color' => 'bg-danger'],
        ];
    }
}
