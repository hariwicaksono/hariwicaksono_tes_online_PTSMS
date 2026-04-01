<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LoggableObserver
{
    public function created($model)
    {
        $this->log('create', $model);
    }

    public function updated($model)
    {
        $this->log('update', $model);
    }

    public function deleted($model)
    {
        $this->log('delete', $model);
    }

    protected function log($action, $model)
    {
        $className = class_basename($model); // Contoh: User, Role
        $nameField = $model->name ?? $model->title ?? $model->id;

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'module'      => $className,
            'description' => "{$action} {$className}: {$nameField}",
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
