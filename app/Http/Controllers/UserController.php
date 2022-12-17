<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {

    public function SignUp(Request $request)
    {
        try {
            $EmailExist = User::where('email', $request->email)->first();
            if (!$EmailExist) {
                $user = new User();
                $user->fullname = $request->username;
                $user->email = $request->email;
                $user->save();
                $account = new Account();
                $account->username = $request->username;
                $account->pwd = bcrypt($request->password);
                $account->user_id = $user->USER_ID;
                $account->account_role = 'User';
                $account->save();
                setcookie("StudyMate", $user->USER_ID, time() + 60 * 60 * 24, "/");
                return response()->json([
                    'status' => 200,
                    'message' => "Create account success"
                ]);
            }
            return response()->json([
                'status' => 400,
                'message' => "Account already existed"
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 400,
                'message' => $th
            ]);
        }
    }

    public function SignIn(Request $request)
    {
        $status_code = 200;
        $message = "Đăng nhập thành công";
        try {
            $User = User::where('email', $request->username)->first();
            if ($User) {
                if (!password_verify($request->password, $User->PWD)) {
                    $status_code = 400;
                    $message = "Wrong email or password";
                } else {
                    setcookie("StudyMate", $ID, time() + 60 * 60 * 24, "/");
                }
            } else {

                $status_code = 400;
                $message = "Wrong email or password";
            }
            return response()->json([
                'status' => $status_code,
                'message' => $message,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 404,
                'message' => $ID,
            ]);
        }
    }

    public function GetProfile(Request $request)
    {
        try {
            $id = $request->user_id;
            $list_posts = DB::select("SELECT post.POST_ID, POST_NAME, IMG, FULLNAME, users.USER_ID FROM posts, users
            WHERE posts.USER_ID = users.USER_ID and posts.POST_ID = posts.CHAPTER_ID
            and POSTs.POST_ID 'Email hoặc username không tồn tại'= posts.POST_ID and users.USER_ID = POSTs.AUTHOR_ID and users.USER_ID = $id");

            return response()->json([
                'status_code' => 200,
                'li_posts' => $list_posts
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 422,
                'message' => $th->getMessage()
            ]);
        }
    }



    public function Follow(Request $request) {
        try {
            $user_id = $request->user_id;
            $wanna_fl_user_id = $request->wanna_fl_user_id;
            
            $user->followed_id = $request->followed_id;
            $user->update();
            return response()->json([
                'status'=> 200,
                'message'=>'User Updated Successfully',
                'User'=>$user,
            ]);
                      
        } catch (\Throwable $th) {
            return response()->json([
                'status'=> 422,
                'message'=>$th->getMessage(),
            ]);
        }
    }

    function UpdateProfile(Request $request)
    {
        try {
            DB::beginTransaction();
            $file = $request->file('profile_img');
            $path = 'img/profile_img';
            $fileName = '';
            if ($file && $file->getClientOriginalExtension()) {
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $extension;
            }


            $user_data = json_decode($request->data);
            $user_data = user_data::where('user_data_ID', $user_data->user_dataID)->first();
            $user_data->user_data_NAME = $user_data->user_dataTitle;
            $user_data->FEE = $user_data->Price;
            if ($file && $file->getClientOriginalExtension()) {
                $user_data->IMG = $path . "/" . $fileName;
            }
            $user_data->user_data_DESC = $user_data->Description;
            $user_data->user_data_STATE = 'Công khai';
            $user_data->COMMISSION = $user_data->Commission;
            $user_data->user_data_NAME = $user_data->user_dataTitle;
            $user_data->user_data_TYPE_ID = $user_data->SubCategory;

            $user_data->save();
            user_data_Require::where('user_data_ID', $user_data->user_dataID)->delete();
            user_data_Gain::where('user_data_ID', $user_data->user_dataID)->delete();
            foreach ($user_data->ListIn as $value) {
                $user_data_require = new user_data_Require();
                $user_data_require->CONTENT = $value->CONTENT;
                $user_data_require->user_data_ID = $user_data->user_data_ID;
                $user_data_require->save();
            }
            foreach ($user_data->ListOut as $value) {
                $user_data_gain = new user_data_Gain();
                $user_data_gain->CONTENT = $value->CONTENT;
                $user_data_gain->user_data_ID = $user_data->user_data_ID;
                $user_data_gain->save();
            }
            foreach ($user_data->Listuser_data as $value) {
                $user_data_chapter = new user_data_Chapter();
                if ($value->id) {
                    $user_data_chapter = user_data_Chapter::where('user_data_CHAPTER_ID', $value->id)->first();
                }
                $user_data_chapter->CHAPTER_NAME = $value->title;
                $user_data_chapter->user_data_ID = $user_data->user_data_ID;
                $user_data_chapter->save();
                $Array2 = $value->POSTS;
                foreach ($Array2 as $value2) {
                    $POSTS = new POSTS();
                    if ($value2->id) {
                        $POSTS = POSTS::where('USER_ID', $value2->id)->first();
                    }
                    $POSTS->POSTS_NAME = $value2->title;
                    $POSTS->POSTS_URL = $value2->url;
                    $POSTS->DURATION = $value2->duration;
                    $POSTS->CHAPTER_ID = $user_data_chapter->user_data_CHAPTER_ID;

                    $POSTS->save();
                }
            }
            DB::commit();
            if ($file && $file->getClientOriginalExtension())
                $file->move($path, $fileName);

            return response()->json([
                'status' => 200,
                'message' => 'Update user successfull'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $th->__toString()
            ]);
        }
    }

}