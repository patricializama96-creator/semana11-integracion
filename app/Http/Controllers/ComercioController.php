<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComercioController extends Controller
{
    /**
     * Semana 5 · Routing y Controladores
     * GET /comercios
     *
     * Lista todos los comercios afiliados junto con el conteo de sus
     * transacciones. withCount() evita el problema N+1: en vez de
     * disparar una consulta extra por cada comercio dentro de la vista,
     * trae el conteo ya resuelto en la consulta principal.
     *
     * Misión B (reto opcional): ordenamos por transacciones_count
     * de forma descendente, para que los comercios con más actividad
     * aparezcan primero.
     *
     * Semana 9 · Laboratorio 3: búsqueda por nombre vía GET.
     * Semana 9 · Laboratorio 5: filtro adicional por rubro.
     * when() solo aplica el where() si el parámetro no está vacío.
     */
    public function index(Request $request): View
    {
        $comercios = Comercio::when($request->buscar,
                fn ($q) => $q->where('nombre_comercio',
                    'like', "%{$request->buscar}%"))
            ->when($request->rubro,
                fn ($q) => $q->where('rubro', $request->rubro))
            ->withCount('transacciones')
            ->orderByDesc('transacciones_count')
            ->get();

        return view('comercios.index', compact('comercios'));
    }

    /**
     * GET /comercios/{comercio}
     *
     * Route Model Binding: Laravel convierte automáticamente el
     * parámetro {comercio} de la ruta en una instancia de Comercio
     * (o lanza 404 si no existe).
     *
     * load('transacciones') trae la relación en una sola consulta
     * adicional (no una por cada transacción), evitando el N+1.
     */
    public function show(Comercio $comercio): View
    {
        $comercio->load('transacciones');

        return view('comercios.show', compact('comercio'));
    }
}
