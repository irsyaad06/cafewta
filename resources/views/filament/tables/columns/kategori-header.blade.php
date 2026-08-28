<div x-data @click.stop>
    <select 
        wire:model.live="tableFilters.kategori.value"
        class="text-sm font-medium leading-6 text-gray-950 dark:text-white bg-transparent border-0 border-b-2 border-transparent focus:ring-0 focus:border-primary-600 p-0 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md transition-colors"
        style="padding-right: 2.5rem !important; min-width: 150px; background-position: right 0.5rem center !important;"
    >
        <option value="">Kategori (Semua)</option>
        @foreach(\App\Models\ExpenseCategory::all() as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
</div>
