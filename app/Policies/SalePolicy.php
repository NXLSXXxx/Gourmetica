<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Determina si el usuario puede listar el historial de ventas.
     * Permitido: Admin General (todas las sedes), Admin Sede (su sede), Cajero (su sede).
     * Denegado: Ingeniero y clientes.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSedeAdmin() || $user->isCajero();
    }

    /**
     * Determina si el usuario puede consultar el detalle de una venta.
     * Aislamiento por sede: Admin general accede a todas; Admin sede y Cajero solo a su sede.
     */
    public function view(User $user, Sale $sale): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSedeAdmin() || $user->isCajero()) {
            return (int) $user->headquarter_id === (int) $sale->headquarter_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede acceder a la interfaz o formulario de registro de venta directa.
     */
    public function createAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSedeAdmin() || $user->isCajero();
    }

    /**
     * Determina si el usuario puede registrar efectivamente una venta en la sede especificada.
     * Principio de fail-closed: si headquarterId es nulo o inválido, deniega la escritura.
     */
    public function create(User $user, ?int $headquarterId = null): bool
    {
        if ($headquarterId === null || $headquarterId <= 0) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSedeAdmin() || $user->isCajero()) {
            return (int) $user->headquarter_id === (int) $headquarterId;
        }

        return false;
    }

    /**
     * Determina si el usuario puede declarar la venta ante SUNAT.
     */
    public function declare(User $user, Sale $sale): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSedeAdmin() || $user->isCajero()) {
            return (int) $user->headquarter_id === (int) $sale->headquarter_id;
        }

        return false;
    }
}
