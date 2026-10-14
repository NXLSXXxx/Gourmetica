<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determina si el usuario puede listar pedidos.
     * Permitido: Admin General (todas las sedes), Admin Sede (su sede), Cajero (su sede).
     * Denegado: Ingeniero y clientes.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSedeAdmin() || $user->isCajero();
    }

    /**
     * Determina si el usuario puede consultar el detalle de un pedido.
     * Aislamiento por sede: Admin general accede a todas; Admin sede y Cajero solo a su sede.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSedeAdmin() || $user->isCajero()) {
            return (int) $user->headquarter_id === (int) $order->headquarter_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede modificar el estado o datos de un pedido.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSedeAdmin() || $user->isCajero()) {
            return (int) $user->headquarter_id === (int) $order->headquarter_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede cancelar un pedido.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSedeAdmin() || $user->isCajero()) {
            return (int) $user->headquarter_id === (int) $order->headquarter_id;
        }

        return false;
    }

    /**
     * Determina si el usuario puede imprimir el ticket de un pedido.
     */
    public function printTicket(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }
}
