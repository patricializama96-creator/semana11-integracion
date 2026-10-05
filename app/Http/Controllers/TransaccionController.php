<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarTransaccionRequest;
use App\Models\Comercio;
use App\Models\Transaccion;

class TransaccionController extends Controller
{
    public function create(Comercio $comercio)
    {
        return view('transacciones.create', compact('comercio'));
    }

    public function store(GuardarTransaccionRequest $request)
    {
        $datos = $request->validated();
        $datos['estado'] = $datos['estado'] ?? 'Iniciada';

        $transaccion = Transaccion::create($datos);

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción registrada correctamente.');
    }

    public function edit(Transaccion $transaccion)
    {
        return view('transacciones.edit', compact('transaccion'));
    }

    public function update(GuardarTransaccionRequest $request, Transaccion $transaccion)
    {
        $transaccion->update($request->validated());

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción actualizada correctamente.');
    }

    public function destroy(Transaccion $transaccion)
    {
        // Misión C: bloquear si está completada
        if ($transaccion->estado === 'Completada') {
            return redirect()
                ->route('comercios.show', $transaccion->comercio_id)
                ->with('error', 'No se puede eliminar una transacción completada.');
        }

        // Misión B: guardar datos antes de borrar
        $comercioId = $transaccion->comercio_id;
        $cliente    = $transaccion->cliente_nombre;

        $transaccion->delete();

        return redirect()
            ->route('comercios.show', $comercioId)
            ->with('mensaje', "Transacción de {$cliente} eliminada.");
    }

    // ── Semana 11 (viernes): mover estado ─────────────────────────
    public function moverEstado(Transaccion $transaccion, string $estado)
    {
        $validos = ['Iniciada', 'Completada', 'Fallida'];

        // 1. ¿El estado existe?
        if (!in_array($estado, $validos, true)) {
            abort(400, 'Estado no válido.');
        }

        // 2. ¿La transición desde el estado actual tiene sentido?
        if (!$transaccion->puedeMoverseA($estado)) {
            abort(400, "No se puede mover de {$transaccion->estado} a {$estado}.");
        }

        $transaccion->update(['estado' => $estado]);

        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', "Transacción movida a {$estado}.");
    }
}
