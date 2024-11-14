<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::where('user_id', $user->id)->get();

        // return response()->json($tasks);
        // return new TaskCollection($tasks);
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return response()->json($request->all());

        // Validasi data yang diterima
        $validator = Validator::make(
            // input data request
            $request->all(),
            // rules validasi
            [
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string',
                'description' => 'required|string',
                'due_date' => 'required|date_format:Y-m-d|after:today',
                'due_time' => 'required|date_format:H:i'
            ],
            // custom message
            [
                'user_id.required' => 'User ID tidak boleh kosong',
                'user_id.exists' => 'User ID tidak ditemukan',
                'title.required' => 'Judul tidak boleh kosong',
                'description.required' => 'Deskripsi tidak boleh kosong',
                'due_date.required' => 'Tanggal jatuh tempo tidak boleh kosong',
                'due_date.date_format' => 'Format tanggal jatuh tempo tidak valid',
                'due_date.after' => 'Tanggal jatuh tempo minimal hari ini',
                'due_time.required' => 'Waktu jatuh tempo tidak boleh kosong',
                'due_time.date_format' => 'Format waktu jatuh tempo tidak valid'
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()
                ],
                400
            );
        };

        // Mengambil data request
        $newTask = $this->convertDateTime($validator->validated());;

        // Nilai default completed
        $newTask['completed'] = false;

        $task = Task::create($newTask);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mengecek apakah id adalah numerik
        if (!ctype_digit($id)) {
            return response()->json(['message' => 'ID harus numerik'], 400);
        }
        $task = Task::findOrFail($id);

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Mengecek apakah id adalah numerik
        if (!ctype_digit($id)) {
            return response()->json(['message' => 'ID harus numerik'], 400);
        }

        // Mengecek apakah data ditemukan
        $task = Task::findOrFail($id);

        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'string',
                'description' => 'string',
                'due_date' => 'date_format:Y-m-d|after:today',
                'due_time' => 'date_format:H:i'
            ],
            [
                'due_date.date_format' => 'Format tanggal jatuh tempo tidak valid',
                'due_date.after' => 'Tanggal jatuh tempo minimal hari ini',
                'due_time.date_format' => 'Format waktu jatuh tempo tidak valid'
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()
                ],
                400
            );
        };

        $updateTask = $this->convertDateTime($validator->validated());

        $task->update($updateTask);

        return response()->json($task, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Mengecek apakah id adalah numerik
        if (!ctype_digit($id)) {
            return response()->json(['message' => 'ID harus numerik'], 400);
        }

        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(null, 204);
    }

    private function convertDateTime($data)
    {
        // Memformat tanggal dan waktu menjadi satu kolom
        $data['deadline'] = $data['due_date'] . ' ' . $data['due_time'];
        unset($data['due_date'], $data['due_time']);
        return $data;
    }

    public function completeTask(string $id)
    {
        // Mengecek apakah id adalah numerik
        if (!ctype_digit($id)) {
            return response()->json(['message' => 'ID harus numerik'], 400);
        }

        // Mengecek apakah data ditemukan
        $task = Task::findOrFail($id);

        $task->update(['completed' => true]);

        return response()->json($task, 200);
    }

    public function testTask(Request $request)
    {
        return response()->json($request->user());
    }
}
