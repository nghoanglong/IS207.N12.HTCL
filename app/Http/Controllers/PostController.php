<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\post;
use App\Models\post_Require;
use App\Models\post_Gain;
use App\Models\post_Chapter;
use App\Models\post_Review;
use App\Models\post_MainType;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Learning;
use App\Models\Approval;
use App\Models\Notification;
use App\Models\posts;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use App\Models\post_SubType;
use App\Models\MongoDB;

use MongoDB\BSON\ObjectId;

class PostController extends Controller
{
    public function GetHomePage()
    {
        try {
            $result1 = array();
            $result2 = array();
            $posts = DB::table('posts')->where('TYPE_NAME', 'self_created')->first();
            foreach ($p as $posts) {
                $total_upvote = DB::table('post_reviews')
                    ->where('post_id', $post->post_id)
                    ->where('post_review_state', 1)
                    ->select(DB::raw('COUNT(*) as numOfUpvote'))
                    ->get();
                $total_downvote = DB::table('post_reviews')
                    ->where('post_id', $post->post_id)
                    ->where('post_review_state', 0)
                    ->select(DB::raw('COUNT(*) as numOfDownvote'))
                    ->get();
                $obj = (object)[
                    'post' => $post
                ];
                array_push($result1, $obj);
            }
            $result = (object)['self_created' => $result1, 'created_from_another' => $result2];
            return response()->json([
                'status' => 200,
                'message' => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 200,
                'message' => $th->getMessage()
            ]);
        }
    }
    
    public function CreateorPost(Request $request)
    {
        try {
            $mg = new MongoDB();
            $post_data = $mg->db->Approval->findOneAndDelete(array('_id' => new ObjectId($request->_id['$oid'])));

            DB::beginTransaction();


            $post = new post();
            $post->post_NAME = $post_data->postTitle;
            $post->IMG = $post_data->Image;
            $post->post_DESC = $post_data->Description;
            $post->COMMISSION = $post_data->Commission;
            $post->post_NAME = $post_data->postTitle;
            $post->AUTHOR_ID = $post_data->Author;
            $post->post_TYPE_ID = $post_data->SubCategory;

            $post->save();

            foreach ($post_data->ListIn as $value) {
                $post_require = new post_Require();
                $post_require->CONTENT = $value->CONTENT;
                $post_require->post_ID = $post->post_ID;
                $post_require->save();
            }
            foreach ($post_data->ListOut as $value) {
                $post_gain = new post_Gain();
                $post_gain->CONTENT = $value->CONTENT;
                $post_gain->post_ID = $post->post_ID;
                $post_gain->save();
            }

            DB::commit();
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'thành công'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $th->__toString(),
            ]);
        }
    }

    public function GetPostDetail()
    {
        $list_posts = DB::table('posts')
            ->select('posts.post_id', 'posts.post_name', 'posts.fee', 'posts.post_desc', 'posts.img', 'users.fullname', 'posts.author_id')
            ->join('users', 'posts.author_id', '=', 'users.user_id')->where('post_STATE', 'self_created')
            ->take(8)
            ->get();
        $posts = array();
        foreach ($list_posts as $post) {
            $total_upvote = DB::table('post_reviews')
                ->where('post_id', $post->post_id)
                ->where('post_review_state', 1)
                ->select(DB::raw('COUNT(*) as numOfUpvote'))
                ->get();
            $total_downvote = DB::table('post_reviews')
                ->where('post_id', $post->post_id)
                ->where('post_review_state', 0)
                ->select(DB::raw('COUNT(*) as numOfDownvote'))
                ->get();
            $obj = (object)[
                'post' => $post,
                'upVote' => $total_upvote,
                'downVote' => $total_downvote
            ];
            array_push($posts, $obj);
        }
        return response()->json([
            'status' => 200,
            'message' => $posts,
        ]);
    }

    

    public function GetpostsComment(Request $request)
    {
        try {
            $id = $request->subtypeId;
            $list_post = DB::table('posts')
                ->select('posts.post_id', 'posts.post_name', 'posts.fee', 'posts.post_desc', 'posts.img', 'users.fullname', 'posts.author_id')
                ->join('users', 'posts.author_id', '=', 'users.user_id')
                ->where('posts.post_type_id', $id)->where('post_STATE', 'self_created')
                ->get();
            $posts = array();
            foreach ($list_post as $post) {
                $total_upvote = DB::table('post_reviews')
                    ->where('post_id', $post->post_id)
                    ->where('post_review_state', 1)
                    ->select(DB::raw('COUNT(*) as numOfUpvote'))
                    ->get();
                $total_downvote = DB::table('post_reviews')
                    ->where('post_id', $post->post_id)
                    ->where('post_review_state', 0)
                    ->select(DB::raw('COUNT(*) as numOfDownvote'))
                    ->get();
                $obj = (object)[
                    'post' => $post,
                    'upVote' => $total_upvote,
                    'downVote' => $total_downvote
                ];
                array_push($posts, $obj);
            }
            return response()->json([
                'status' => 200,
                'message' => $posts
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 200,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function SavePost(Request $request)
    {
        try {
            $posts = new posts();
            $posts->USER_ID = $request->user_id;
            $posts->post_ID = $request->post_id;
            $posts->save();
            $posts_id = DB::table('postss')
                ->where('user_id', $request->user_id)
                ->where('post_id', $request->post_id)
                ->select('posts_id')
                ->first();
            $flag = $this->InsertPayment($request->user_id, $request->admin_id, $request->admin_coin, $posts_id->posts_id);
            if ($flag) {
                return response()->json([
                    'status' => 200,
                    'message' => 1
                ]);
            }
            return response()->json([
                'status' => 200,
                'message' => 0
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 200,
                'message' => 0
            ]);
        }
    }

    public function PostSearch()
    {
        try {
            if (isset($_COOKIE['POSTS'])) {
                $id = $_COOKIE['POSTS'];
                $post_list = post::where('AUTHOR_ID', $id)->where('post_STATE', 'self_created')->orWhere('post_STATE', 'self_created')->get();
                $ans = array();

                foreach ($post_list as $post) {
                    $post_review = post_Review::where('post_ID', $post->post_ID);
                    $earn = DB::table('payments')->where('RECEIVER_ID', $id)
                        ->join('postss', 'payments.posts_ID', '=', 'postss.posts_ID')
                        ->where('post_ID', $post->post_ID)->get()->sum('AMOUNT');
                    $subcribe = posts::where('post_ID', $post->post_ID)->count();
                    $object = (object) [
                        'postTitle' => $post->post_NAME,
                        'Created_at' => $post->CREATED_AT,
                        'Price' => $post->FEE,
                        'Commission' => $post->COMMISSION,
                        'Status' => $post->post_STATE,
                        'Rate' => (object) [
                            'up' => $post_review->where('post_REVIEW_STATE', 1)->count(),
                            'down' => $post_review->where('post_REVIEW_STATE', 0)->count()
                        ],
                        'Earn' => $earn,
                        'Subcribe' => $subcribe,
                        'postID' => $post->post_ID,
                        'postIMG' => $post->IMG
                    ];
                    array_push($ans, $object);
                }

                return response()->json([
                    'status' => 200,
                    'message' => $ans,
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Cookies het han',
                    'user' => null
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 400,
                'data' => $th,
            ]);
        }
    }
   
}
