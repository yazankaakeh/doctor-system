<?php

namespace Modules\AdminManagement\Models;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Doctor\Models\Doctor;

class AuditLog extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'url',
        'method',
        'payload',
        'ip',
        'route_name',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public static function IndexFilter($logins, $request)
    {
        // Filter by auditable type (e.g., Doctor, Patient, etc.)
        if (isset($request['auditable_type']) && $request['auditable_type'] != 'all') {
            $logins->where('auditable_type', $request['auditable_type']);
        }

        // Filter by specific auditable ID
        if (isset($request['auditable_id']) && $request['auditable_id'] != 'all') {
            $logins->where('auditable_id', $request['auditable_id']);
        }

        // Filter by route name
        if (isset($request['route_name']) && $request['route_name'] != 'all') {
            $logins->where('route_name', $request['route_name']);
        }

        // Filter by method
        if (isset($request['method']) && $request['method'] != 'all') {
            $logins->where('method', $request['method']);
        }

        // Date range filter
        if ((isset($request['start_date']) && $request['start_date'] != 'undefined') && (isset($request['end_date']) && $request['end_date'] != 'undefined')) {
            $request['start_date'] = str_replace('/', '-', $request['start_date']);
            $request['end_date'] = str_replace('/', '-', $request['end_date']);

            $request['start_date'] = DateTime::createFromFormat('m-d-Y', $request['start_date']);
            $request['start_date'] = $request['start_date']->format('Y-m-d');

            $request['end_date'] = DateTime::createFromFormat('m-d-Y', $request['end_date']);
            $request['end_date'] = $request['end_date']->format('Y-m-d');

            $logins->whereBetween('created_at', [$request['start_date'], $request['end_date']]);
        }

        return $logins;
    }

    public static function filter($logins, $request)
    {
        if (! is_null($request['auditable_type'])) {
            $logins->where('auditable_type', $request['auditable_type']);
        }

        if (! is_null($request['auditable_id'])) {
            $logins->where('auditable_id', $request['auditable_id']);
        }

        if (! is_null($request['created_at'])) {
            $logins->whereDate('created_at', '>=', $request['created_at']);
        }

        return $logins;
    }

    public static function GetAuditableTypes(): array
    {
        return [
            Doctor::class => 'Doctor',
            // Add more auditable types as needed
            // Patient::class => 'Patient',
        ];
    }

    public static function GetAuditableModels(): Collection|array
    {
        return Doctor::query()->select('name', 'id')->get();
    }

    /**
     * Get the auditable model (polymorphic relationship)
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Legacy accessor for backward compatibility.
     *
     * Returns the related Doctor only when this audit row's auditable_type is
     * actually Doctor — otherwise returns null. Uses the already-loaded
     * polymorphic `auditable` relation when available to avoid extra queries.
     *
     * NOTE: the old definition below chained ->where() onto the relationship,
     * which Laravel applies to the *related* table (`doctors`) producing
     * "Unknown column 'auditable_type'" errors. Replaced with a safe accessor.
     *
     * @deprecated Use $audit->auditable (MorphTo) instead.
     */
    public function getDoctorAttribute(): ?Doctor
    {
        if ($this->auditable_type !== Doctor::class) {
            return null;
        }

        if ($this->relationLoaded('auditable')) {
            return $this->auditable instanceof Doctor ? $this->auditable : null;
        }

        return Doctor::query()->find($this->auditable_id);
    }
}
