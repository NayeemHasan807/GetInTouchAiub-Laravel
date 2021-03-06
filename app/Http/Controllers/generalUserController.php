<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\GeneralUser;
use App\Models\User;
use App\Models\GuPost;
use App\Models\GuText;
use App\Models\AdminNotice;
use App\Models\GuPostRequest;
use App\Http\Requests\updateProfileRequest;
use App\Http\Requests\sendtextRequest;
use App\Http\Requests\postnewcontentRequest;
use App\Http\Requests\requesttoapproveRequest;
use App\Http\Requests\requesttocheckidproblemRequest;
use PDF;
use GuzzleHttp\Client;

class generalUserController extends Controller
{
    //home
    public function guhome(){
        $profile = GeneralUser::where('guid',session('username'))->first();
        return view('generalUser.guHome',['profile'=>$profile]);
    }

    //profile
    public function profile(){
       $profile = GeneralUser::where('guid',session('username'))->first();
           return view('generalUser.profile', ['profile'=>$profile]);
    }

    //profileDelete 
     public function profiledelete(){
           $profile = GeneralUser::where('guid',session('username'))->first();
        return view('generalUser.profiledelete',['profile'=>$profile]);
    }

    public function profiledestroy(){
        $generaluser = GeneralUser::where('guid',session('username'))->first();
        if($generaluser->delete())
        {
            $user = User::where('userid',session('username'))->first();
            if($user->delete())
            {
                return redirect('/logout');
            }
        }
    }

    //ProfileUpdate
    public function profileedit(){
        $profile = GeneralUser::where('guid',session('username'))->first();
        return view('generalUser.profileedit',['profile'=>$profile]);
    }
    public function profileupdate(updateProfileRequest $req){
           
               
        $profile = GeneralUser::where('guid',session('username'))->first();

        $profile->name              = $req->name;
        $profile->email             = $req->email;
        $profile->dob               = $req->dob;
        $profile->address           = $req->address;
        $profile->save();
        
        return redirect()->route('generalUser.profile');
                      
    }

    //All Post
    public function allpost(){
        $allpost = GuPost::all();
        return view('generalUser.allpost',['allpost'=>$allpost]);
    }

    //search any post
    public function searchanypost(Request $req){
   
        $allpost = GuPost::where('text','like','%'.$req->key.'%')->get();          
        return json_encode($allpost);
    }

    //search gu
    public function searchgu(){
        return view('generalUser.searchgu');
    }
    public function searchguresult(Request $req){
        $gulists = GeneralUser::where('guid','like','%'.$req->key.'%')->get();          
        return json_encode($gulists);
    }


    //send text
    public function sendtext(){
        return view('generalUser.sendtext');
    }
    public function sendtextsave(sendtextRequest $req){
        $sendtext = new GuText();
        $sendtext->guid         =   session('username'); 
        $sendtext->text         =   $req->text;
        $sendtext->receiverid   =   $req->receiverid;
        if($sendtext->save()){
            $req->session()->flash('msg', 'Message Send Successfully!!!...');
            return redirect()->route('generalUser.sendtext');
        }
    }

    //received text
     public function receivetext(){
        $receivetext = GuText::all();
        return view('generalUser.receivetext',['receivetext'=>$receivetext]);
    }

    //view notice
     public function viewnotice(){
        // $client     = new Client();
        // $res        = $client->request('GET', 'http://127.0.0.1:3000/acnotice/noticesAPI');
        // $viewnotice    = json_decode($res->getBody());
        // //print_r($notices);
        // return view('accountController.viewnotice',['viewnotice'=>$viewnotice]);
        $viewnotice = AdminNotice::all();
        return view('generalUser.viewnotice',['viewnotice'=>$viewnotice]);
    }

    //Post new content
    public function postnewcontent(){
        return view('generalUser.postnewcontent');
    }
    public function postnewcontentsave(postnewcontentRequest $req){
        if($req->hasFile('file')){
            $file = $req->file('file');
            $name = time().$file->getClientOriginalName();
            if($file->move('assets/generalUser/post', $name)){
                $postnewcontent = new GuPostRequest();
                $postnewcontent->guid         =   session('username'); 
                $postnewcontent->text         =   $req->text;
                $postnewcontent->file         =   "/assets/generalUser/post/".$name;
                if($postnewcontent->save()){
                    $req->session()->flash('msg', 'Post send!');
                    return redirect()->route('generalUser.postnewcontent');
                }
            }
        }
        else{
            $postnewcontent = new GuPostRequest();
            $postnewcontent->guid         =   session('username'); 
            $postnewcontent->text         =   $req->text;
            if($postnewcontent->save()){
                $req->session()->flash('msg', 'Post send!');
                return redirect()->route('generalUser.postnewcontent');
            }
        }
    }

    //My Post
    public function mypost(){
        return view('generalUser.mypost');
    }

    public function mypostlist(){
        $postlist = GuPost::where('guid',session('username'))->get();
        return view('generalUser.mypostlist',['postlist'=>$postlist]);
    }
    public function editpost($id){
        $post = GuPost::find($id);
        return view('generalUser.mypostedit' , $post);
    }
    public function editpostsave($id , postnewcontentRequest $req){
        if($req->hasFile('file')){
            $file = $req->file('file');
            $name = time().$file->getClientOriginalName();
            if($file->move('assets/generalUser/post', $name)){
                $post = GuPost::find($id);
                $post->guid         =   session('username'); 
                $post->text         =   $req->text;
                $post->file         =   "/assets/generalUser/post/".$name;
                if($post->save()){
                    return redirect()->route('generalUser.mypostlist');
                }
            }
        }
        else{
            $post = GuPost::find($id);
            $post->guid         =   session('username'); 
            $post->text         =   $req->text;
            if($post->save()){
                return redirect()->route('generalUser.mypostlist');
            }
        }
    }
    public function deletepost($id){
        $post = GuPost::find($id);
        return view('generalUser.mypostdelete' , $post);
    }
    public function deletepostsave($id){
        $post = GuPost::find($id);
        if($post->delete())
        {
            return redirect()->route('generalUser.mypostlist');
        }
    }
    public function pendingpostlist(){
        $pendingpostlist = GuPostRequest::where('guid',session('username'))->get();
        return view('generalUser.pendingpostlist',['pendingpostlist'=>$pendingpostlist]);
    }

    //API

    //request to approve
    public function requesttoapprove(){
        return view('generalUser.requesttoapprove');
    }
    public function requesttoapprovesend(requesttoapproveRequest $req){
        //guzzle http request
        $client  = new Client();
        $res     = $client->request('POST', 'http://127.0.0.1:3000/userController/requesttoapprove/API', [
            'form_params'   => [
                'guid'      =>  session('username'),   
                'towhom'   =>  $req->towhom,
                'actiontype'      =>  $req->actiontype,
                'text'    =>  $req->text
            ]
        ]);
        $response    = json_decode($res->getBody());
        if($response->result == "Request Sent Successfully!")
        {
            $req->session()->flash('msg', "Request Sent Successfully!");
            return redirect()->route('generalUser.requesttoapprove');
        }
        else
        {
            echo $response->result;
        }
    }


    //request to check id problem
    public function requesttocheckidproblem(){
        return view('generalUser.requesttocheckidproblem');
    }
    public function requesttocheckidproblemsend(requesttocheckidproblemRequest $req){
        //guzzle http request
        $client  = new Client();
        $res     = $client->request('POST', 'http://127.0.0.1:3000/userController/requesttocheckidproblem/API', [
            'form_params'   => [
                'guid'      =>  session('username'),   
                'towhom'   =>  $req->towhom,
                'actiontype'      =>  $req->actiontype,
                'text'    =>  $req->text
            ]
        ]);
        $response    = json_decode($res->getBody());
        if($response->result == "Request Sent Successfully!")
        {
            $req->session()->flash('msg', "Request Sent Successfully!");
            return redirect()->route('generalUser.requesttocheckidproblem');
        }
        else
        {
            echo $response->result;
        }
    }

    //report
    public function report(){
        return view('generalUser.gureport');
    }

    public function postreport(){
        $postlist = GuPost::where('guid',session('username'))->get();
        $postcount=count($postlist);
        $pendingpostlist = GuPostRequest::where('guid',session('username'))->get();
        $pendingpostcount=count($pendingpostlist);
        $pdf = PDF::loadView('generalUser.postreport', ['postlist'=>$postlist, 'pendingpostlist'=>$pendingpostlist ,'postcount'=>$postcount , 'pendingpostcount'=>$pendingpostcount]);
        return $pdf->download('postreport.pdf');
    }

    public function noticereport(){
        $notices = AdminNotice::all();
        $noticecount=count($notices);
        $pdf = PDF::loadView('generalUser.noticereport', ['notices'=>$notices, 'noticecount'=>$noticecount]);
        return $pdf->download('noticereport.pdf');
    }
}
 