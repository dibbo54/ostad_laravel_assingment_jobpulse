<?php

namespace App\Http\Controllers;

use App\Post\JobPost;
use App\Models\Listing;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Middleware\isEmployer;
use App\Http\Middleware\isPremiumUser;
use App\Http\Requests\JobEditFormRequest;
use App\Http\Requests\JobPostFormRequest;

class PostJobController extends Controller
{
    protected $job;
    public function __construct(JobPost $job)
    {
        $this->job = $job;
        $this->middleware('auth');
        $this->middleware('isPremiumUser')->only(['create', 'store']);
        $this->middleware('isEmployer');
    }

    public function index()
    {
        $jobs = Listing::where('user_id', auth()->user()->id)->get();

        return view('job.index', compact('jobs'));
    }

    public function create()
    {
        return view('job.create');
        // dd('create a job post');
    }

    public function store(JobPostFormRequest $request)
    {
        $this->job->store($request);


        // $imagePath=$request->file('feature_image')->store('images','public');
        // $post=new Listing;
        // $post->feature_image=$imagePath;
        // $post->user_id= auth()->user()->id;
        // $post->title=$request->title;
        // $post->description=$request->description;
        // $post->roles=$request->roles;
        // $post->job_type=$request->job_type;
        // $post->address=$request->address;
        // $post->application_close_date=\Carbon\Carbon::createFromFormat('d/m/Y', trim($request->date))->format('Y-m-d');
        // $post->salary=$request->salary;
        // $post->slug=Str::slug($request->title).'.'.Str::uuid();

        // $post->save();
        return back()->with('success', 'Your job post has been successfully created');




        // return redirect()->route('job.index')->with('success', 'Your job post has been posted');
    }

    public function edit(Listing $listing)
    {
        return view('job.edit', compact('listing'));
    }

    public function update($id, JobEditFormRequest $request)
    {
        $this->job->updatePost($id, $request);

        return back()->with('success', 'Your job post has been successfully updated');
    }

    public function destroy($id)
    {
        Listing::find($id)->delete();

        return back()->with('success', 'Your job post has been successfully deleted');
    }
}
