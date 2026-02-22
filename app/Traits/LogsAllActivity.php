<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Str;

trait LogsAllActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Menggunakan nama model sebagai nama log secara dinamis
            ->useLogName(class_basename($this))
            // Mencatat semua atribut yang bisa diisi ($fillable)
            ->logFillable()
            // Hanya mencatat jika ada perubahan
            ->logOnlyDirty()
            // Deskripsi event yang dinamis
            ->setDescriptionForEvent(fn(string $eventName) => "Data ini telah di-{$eventName}");
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $request = request();
        if (! $request) {
            return;
        }

        $requestId = $this->resolveRequestId($request);

        $meta = array_filter([
            'request_id' => $requestId,
            'url' => $this->resolveRequestUrl($request),
            'ip' => $request->ip(),
            'route_name' => $request->route()?->getName(),
            'user' => auth()->user()?->name,
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'active_branch_id' => session('active_branch_id'),
            'subject_branch_id' => $this->resolveSubjectBranchId(),
            'business_key' => $this->resolveBusinessKey(),
        ], fn ($value) => filled($value));

        if (empty($meta)) {
            return;
        }

        $existingMeta = data_get($activity->properties, 'meta', []);
        if (! is_array($existingMeta)) {
            $existingMeta = (array) $existingMeta;
        }

        $properties = collect($activity->properties ?? []);
        $properties->put('meta', array_merge($existingMeta, $meta));
        $activity->properties = $properties;
    }

    protected function resolveRequestId($request): string
    {
        $requestId = $request->attributes->get('request_id')
            ?: $request->headers->get('X-Request-Id')
            ?: $request->headers->get('X-Correlation-Id');

        if (! filled($requestId)) {
            $requestId = (string) Str::uuid();
        }

        $request->attributes->set('request_id', $requestId);

        return (string) $requestId;
    }

    protected function resolveRequestUrl($request): string
    {
        $isLivewireRequest = $request->headers->has('X-Livewire')
            || $request->is('livewire/*')
            || $request->route()?->getName() === 'livewire.update';

        if ($isLivewireRequest) {
            $referer = $request->headers->get('referer');
            if (filled($referer)) {
                return $referer;
            }
        }

        return $request->fullUrl();
    }

    protected function resolveSubjectBranchId(): ?int
    {
        $branchId = data_get($this, 'branch_id');

        if (! filled($branchId)) {
            return null;
        }

        return (int) $branchId;
    }

    protected function resolveBusinessKey(): ?string
    {
        $candidates = [
            'order_no',
            'invoice_no',
            'quotation_no',
            'payment_no',
            'transaction_no',
            'reference_no',
            'code',
            'sku',
            'email',
            'name',
        ];

        foreach ($candidates as $field) {
            $value = data_get($this, $field);
            if (! filled($value)) {
                continue;
            }

            return $field . ':' . $value;
        }

        return null;
    }
}
