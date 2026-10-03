<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        // withCount agrega productos_count a cada categoría sin cargar sus productos
        $categorias = Categoria::withCount('productos')
            ->orderBy('nombre')
            ->paginate(10);

        return view('categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        return view('categorias.create', ['categoria' => new Categoria]);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $categoria = Categoria::create($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('exito', "Categoría «{$categoria->nombre}» creada.");
    }

    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(CategoriaRequest $request, Categoria $categoria): RedirectResponse
    {
        $categoria->update($request->validated());

        return redirect()
            ->route('categorias.index')
            ->with('exito', "Categoría «{$categoria->nombre}» actualizada.");
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        // Con eliminado lógico la llave foránea ya no interviene (la fila no se borra),
        // así que esta comprobación es la que evita dejar productos vigentes sin categoría
        if ($categoria->productos()->exists()) {
            return back()->with('error', "No se puede eliminar «{$categoria->nombre}» porque tiene productos. Elimínalos o cámbialos de categoría primero.");
        }

        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with('exito', "Categoría «{$categoria->nombre}» eliminada.");
    }
}
