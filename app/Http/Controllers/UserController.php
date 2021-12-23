<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $data = User::select('*')->orderBy('created_at', 'DESC')->where('id','!=', Auth::user()->id);
            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('name', function ($data) {
                    return "<img width='50px' class='rounded img-thumbnail' src='".asset('storage/user/'.$data->profile_picture)."'/>"." ".$data->name;
                })
                ->addColumn('action', function ($data) {
                    $funsi_delete="deleteData($data->id,'$data->name')";
                    $button = [
                        '<button title="Edit" onclick="editFormModal('.$data->id.')"
                            class="btn btn-sm btn-warning m-1" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="fas fa-edit"></i>
                        </button>',
                        '<button title="Hapus" onClick="'.$funsi_delete.'"
                            class="btn btn-sm btn-warning m-1" data-bs-toggle="tooltip" data-bs-placement="top">
                            <i class="fas fa-trash"></i>
                        </button>',
                    ];
                    return implode("",$button);
                })
                ->rawColumns(['action','name'])
                ->toJson();
        }else{
            return view('app.user.index');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required',
            'email'             => 'email|required',
            'password'          => 'required',
            'profile_picture'   => 'required|mimes:jpeg,png,jpg,gif|max:5052',
        ]);

        $extension = $request->file('profile_picture')->extension();
        $filename = Str::random("20").".".$extension;
        Storage::putFileAs('public/user',  $request->file('profile_picture'), $filename);

        $user = User::create([
            // 'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'profile_picture'   => $filename
        ]);
        
        if($user){
            return redirect(route('user'))->with('message', [
                'status'    => 'success',
                'message'   => 'New User has been added!'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if($request->ajax()){
            return response()->json(User::find($id));
        }else{
            return response()->json(['this route should be request from ajax'], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'              => 'required',
            'email'             => 'email|required',
            'password'          => 'required',
            'profile_picture'   => 'mimes:jpeg,png,jpg,gif|max:5052',
        ]);

        $user = User::find($id);
        if($request->file('profile_picture')!=null){
            $extension = $request->file('profile_picture')->extension();
            $filename = Str::random("20").".".$extension;
            if($user->profile_picture!="default.png"){
                if(Storage::exists('public/user/'.$user->profile_picture)){
                    Storage::delete('public/user/'.$user->profile_picture);
                }
            }
            Storage::putFileAs('public/user',  $request->file('profile_picture'), $filename);
            $user->profile_picture = $filename;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;

        if($user->save()){
            return redirect(route('user'))->with('message', [
                'status'    => 'success',
                'message'   => 'User '.$request->name.' has been updated!'
            ]);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if($request->ajax()){
            $user = User::find($id);
            if($user->profile_picture!="default.png"){
                if(Storage::exists('public/user/'.$user->profile_picture)){
                    Storage::delete('public/user/'.$user->profile_picture);
                }
            }
            if($user->delete()){
                return response()->json(['success'], 200);
            }
        }else{
            return response()->json(['this route should be request from ajax'], 400);
        }
    }
}
