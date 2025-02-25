<?php

use Livewire\Volt\Component;
use App\Models\Todo;
use App\Models\SmallSteps;

new class extends Component {
    public Todo $todo;
    public string $todoName = '';
    public string $todoDescription = '';
    public $smallSteps = [];

    public function createTodo()
    {
        $this->validate([
            'todoName' => 'required|string|max:255|min:3',
            'todoDescription' => 'nullable|string|max:500|min:3',
        ]);

        $todo = Auth::user()
            ->todos()
            ->create([
                'name' => $this->todoName,
                'description' => $this->todoDescription,
            ]);

        foreach ($this->smallSteps as $step) {
            $todo->smallSteps()->create([
                'name' => $step['name'],
                'description' => $step['description'],
                'completed' => false,
            ]);
        }

        $this->resetFields();
    }

    public function deleteTodo($id)
    {
        $todo = Auth::user()->todos()->findOrFail($id);
        $this->authorize('delete', $todo);

        $todo->delete();
    }

    public function with()
    {
        return [
            'todos' => Auth::check() ? Auth::user()->todos()->with('smallSteps')->get() : collect(),
        ];
    }

    public function addSmallStep()
    {
        $this->smallSteps[] = ['name' => '', 'description' => '']; // Adiciona um novo passo em branco
    }

    public function removeSmallStep($index)
    {
        unset($this->smallSteps[$index]); // Remove o passo pelo índice
        $this->smallSteps = array_values($this->smallSteps); // Reindexa o array
    }

    public function toggleTodoCompletion($id)
    {
        $todo = Auth::user()->todos()->findOrFail($id);
        $this->authorize('update', $todo);

        $todo->update(['completed' => !$todo->completed]);
    }

    public function toggleSmallStepCompletion($id)
    {
        $step = SmallSteps::findOrFail($id);
        $step->update(['completed' => !$step->completed]);

        // Se todos os passos estiverem completos, marcar a tarefa como concluída
        $todo = $step->todo;
        if ($todo->smallSteps()->where('completed', false)->count() == 0) {
            $todo->update(['completed' => true]);
        } else {
            $todo->update(['completed' => false]);
        }
    }

    public function resetFields()
    {
        $this->reset(['todoName', 'todoDescription', 'smallSteps']);
    }
}; ?>


<div>
    <h1 class="text-2xl font-bold mb-4">Todo List Manager</h1>
    <form wire:submit='createTodo' class="flex flex-col gap-4 mb-4">
        <label for="todoName">Create Task:</label>
        <x-text-input wire:model='todoName' placeholder="Task name..." class="p-2 border rounded" />

        <textarea wire:model='todoDescription' placeholder="Task description..." class="p-2 border rounded"></textarea>

        <label for="smallSteps">Task Steps:</label>
        <div class="steps-block">
            @foreach ($this->smallSteps as $index => $step)
                <div class="flex flex-col gap-4 mb-4 px-4 py-4">
                    <label for="smallSteps">Step {{ $index + 1 }}</label>
                    <x-text-input wire:model="smallSteps.{{ $index }}.name" placeholder="Sub-Task name..."
                        class="p-2 border rounded" />
                    <textarea wire:model="smallSteps.{{ $index }}.description" placeholder="Sub-Task description..."
                        class="p-2 border rounded"></textarea>
                    <button type="button" wire:click="removeSmallStep({{ $index }})"
                        class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-700">Remove</button>
                </div>
            @endforeach
        </div>

        <button type="button" wire:click="addSmallStep"
            class="w-full px-3 py-1 bg-green-500 text-white rounded hover:bg-green-700 transition">
            Add Step
        </button>

        <div class="flex justify-end gap-2">
            <button type="button" wire:click="resetFields"
                class="w-full px-3 py-1 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">
                Reset
            </button>

            <button type="submit" class="w-full px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-700 transition">
                Create Task
            </button>
        </div>

        <x-input-error :messages='$errors->get("todoName")' class="text-red-500" />
        <x-input-error :messages='$errors->get("todoDescription")' class="text-red-500" />
    </form>

    <div class="overflow-x-auto">
        {{-- <table class="w-full border-collapse bg-white rounded-lg shadow-md"">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-3 text-center">✔</th>
                    <th class="p-3 text-center">Task</th>
                    <th class="p-3 text-center">Description</th>
                    <th class="p-3 text-center">Created</th>
                    <th class="p-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($todos as $todo)
                    <tr wire:key='todo-{{ $todo->id }}' class="border-b hover:bg-gray-50">
                        <td class="p-3 w-1/4 text-center">
                            <input type="checkbox" wire:click='toggleTodoCompletion({{ $todo->id }})'
                                class="h-5 w-5 text-blue-500" {{ $todo->completed ? 'checked' : '' }}>
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <span class="{{ $todo->completed ? 'line-through text-gray-500' : '' }}">
                                {{ $todo->name }}
                            </span>
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <p class="text-gray-500 text-sm">{{ $todo->description }}</p>
                        </td>
                        <td class="p-3 w-1/4 text-center text-gray-500">
                            {{ $todo->created_at->diffForHumans() }}
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <button wire:click='deleteTodo({{ $todo->id }})'
                                class="text-red-500 hover:text-red-700">
                                🗑️
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table> --}}

        <table class="w-full border-collapse bg-white rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="p-3 text-center">✔</th>
                    <th class="p-3 text-center">Task</th>
                    <th class="p-3 text-center">Description</th>
                    <th class="p-3 text-center">Steps</th>
                    <th class="p-3 text-center">Created</th>
                    <th class="p-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($todos as $todo)
                    <tr wire:key="todo-{{ $todo->id }}" class="border-b hover:bg-gray-50">
                        <td class="p-3 w-1/4 text-center">
                            <input type="checkbox" wire:click="toggleTodoCompletion({{ $todo->id }})"
                                class="h-5 w-5 text-blue-500" {{ $todo->completed ? 'checked' : '' }}>
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <span class="{{ $todo->completed ? 'line-through text-gray-500' : '' }}">
                                {{ $todo->name }}
                            </span>
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <p class="text-gray-500 text-sm">{{ $todo->description }}</p>
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <ul>
                                @if ($todo->smallSteps->isEmpty())
                                    <p class="text-gray-500 italic">No subtasks</p>
                                @else
                                    @foreach ($todo->smallSteps as $step)
                                        <li>
                                            <input type="checkbox"
                                                wire:click="toggleSmallStepCompletion({{ $step->id }})"
                                                class="h-4 w-4 text-green-500" {{ $step->completed ? 'checked' : '' }}>
                                            {{ $step->name }}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </td>
                        <td class="p-3 w-1/4 text-center text-gray-500">
                            {{ $todo->created_at->diffForHumans() }}
                        </td>
                        <td class="p-3 w-1/4 text-center">
                            <button wire:click="deleteTodo({{ $todo->id }})"
                                class="text-red-500 hover:text-red-700">
                                🗑️
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
