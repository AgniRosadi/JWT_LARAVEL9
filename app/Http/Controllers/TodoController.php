<?php

namespace App\Http\Controllers;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $todos = Todo::all();
        return response()->json([
            'status' => 'success',
            'todos' => $todos,
        ]);
    }

    public function store(Request $request)
    {
        $data = DB::table('todos')->get();
        $title = [];
        $desc = [];
        foreach ($data as $res)
        {
            $title = $res->title;
            $desc = $res->description;
        }

        $response = response()->json([
            'status' => 'error',
            'message' => 'Error, Data can not be empty',
            'error_code' => 400
        ]);

        $rules =([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);
        

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return $response;
        }

        if($title == $request->title || $desc == $request->title){
            return response()->json([
                'status' => 'error',
                'message' => 'Error, Data already exists',
                'error_code' => 401
            ]);
        }

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description
        ]);

        return response()->json([
            'status' => 'success',
            'todo' => $todo,
        ]);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);
        
        $todo = Todo::find($id);
        $todo->title = $request->title;
        $todo->description = $request->description;
        $todo->sava();


        return response()->json([
            'status' => 'success',
            'messages' => 'Todo updated successfully',
            'todo' => $todo

        ]);
    }

    public function destroy($id)
    {
        $todo = Todo::find($id);
        $todo->delete();

        return response()->json([
            'status' => 'success',
            'messages' => 'Todo deleted successfully',
            'todo' => $todo,
        ]);
    }
}
