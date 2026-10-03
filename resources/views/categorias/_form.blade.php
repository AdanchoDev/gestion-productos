{{-- Formulario compartido por crear y editar. old() conserva lo capturado cuando la validación falla --}}
<div class="grid gap-4">
    <div>
        <label for="nombre" class="mb-1 block text-sm font-medium text-slate-700">Nombre</label>
        <input id="nombre" name="nombre" type="text" maxlength="100" value="{{ old('nombre', $categoria->nombre) }}" autofocus
            @error('nombre') aria-invalid="true" aria-describedby="error-nombre" @enderror
            @class([
                'w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                'border-red-400 focus:ring-red-200' => $errors->has('nombre'),
                'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200' => ! $errors->has('nombre'),
            ])>
        @error('nombre') <p id="error-nombre" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="descripcion" class="mb-1 block text-sm font-medium text-slate-700">Descripción <span class="font-normal text-slate-400">(opcional)</span></label>
        <textarea id="descripcion" name="descripcion" rows="3"
            @error('descripcion') aria-invalid="true" aria-describedby="error-descripcion" @enderror
            @class([
                'w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                'border-red-400 focus:ring-red-200' => $errors->has('descripcion'),
                'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200' => ! $errors->has('descripcion'),
            ])>{{ old('descripcion', $categoria->descripcion) }}</textarea>
        @error('descripcion') <p id="error-descripcion" class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-2">
    <a href="{{ route('categorias.index') }}"
        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
        Cancelar
    </a>
    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        Guardar
    </button>
</div>
