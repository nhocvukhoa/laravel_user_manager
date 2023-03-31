<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::join('departments', 'departments.id', '=', 'users.department_id')
            ->select(
                'users.*',
                'departments.name as departments',
            )
            ->get();

        return response()->json(['data' => $users]);
    }

    public function create()
    {
        $departments = Department::select('id as value', 'name as label')->get();

        return response()->json([
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                "username" => "required|unique:users,username",
                "name" => "required|max:255",
                "email" => "required|email",
                "department_id" => "required",
                "password" => "required|confirmed"
            ],
            [
                "username.required" => "Tên tài khoản là bắt buộc",
                "username.unique" => "Tên tài khoản đã tồn tại",
                "name.required" => "Họ tên là bắt buộc",
                "name.max" => "Họ tên tối đa 255 ký tự",
                "email.required" => "Email là bắt buộc",
                "email.email" => "Email không hợp lệ",
                "department_id.required" => "Phòng ban là bắt buộc",
                "password.required" => "Mật khẩu là bắt buộc",
                "password.confirmed" => "Mật khẩu và Xác nhận mật khẩu không khớp"
            ]
        );

        User::create([
            "status" => UserStatus::ACTIVE,
            "username" => $request["username"],
            "name" => $request["name"],
            "email" => $request["email"],
            "department_id" => $request["department_id"],
            "password" => Hash::make($request["password"])
        ]);
    }

    public function edit($id) 
    {
        $user = User::findOrFail($id);

        $departments = Department::select('id as value', 'name as label')->get();

        return response()->json([
            'user' => $user,
            'departments' => $departments
        ]);
    }

    public function update(Request $request, $id) 
    {
        $validated = $request->validate(
            [
                "username" => "required|unique:users,username,".$id,
                "name" => "required|max:255",
                "email" => "required|email",
                "department_id" => "required",
            ],
            [
                "username.required" => "Tên tài khoản là bắt buộc",
                "username.unique" => "Tên tài khoản đã tồn tại",
                "name.required" => "Họ tên là bắt buộc",
                "name.max" => "Họ tên tối đa 255 ký tự",
                "email.required" => "Email là bắt buộc",
                "email.email" => "Email không hợp lệ",
                "department_id.required" => "Phòng ban là bắt buộc",
            ]
        );

        User::find($id)->update([
            "username" => $request["username"],
            "name" => $request["name"],
            "email" => $request["email"],
            "department_id" => $request["department_id"],
        ]);

        if ($request['change_password'] == true) {
            $validated = $request->validate(
                [
                    "password" => "required|confirmed"
                ],
                [
                    "password.required" => "Mật khẩu là bắt buộc",
                    "password.confirmed" => "Mật khẩu và Xác nhận mật khẩu không khớp"
                ]
            );

            User::find($id)->update([
                "password" => Hash::make($request['password']),
                "change_password_at" => now()
            ]);
        }
    }

    public function destroy($id) {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'Không tìm thấy người dùng',
            ]);
        }

        $user->delete();
        return response()->json([
            'status' => 200,
            'user' => $user
        ]);
    }
}
