<?php

namespace App\Post;

use App\Models\Listing;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobPost
{

    protected $listing;
    public function __construct(Listing $listing)
    {
        $this->listing  = $listing;
    }

    public function getImagePath(Request $data)
    {
        return $data->file('feature_image')->store('images', 'public');
    }

    public function store(Request $data): void
    {
        $imagePath = $this->getImagePath($data);
        $this->listing->feature_image = $imagePath;
        $this->listing->user_id = auth()->user()->id;
        $this->listing->title = $data['title'];
        $this->listing->description = $data['description'];
        $this->listing->roles = $data['roles'];
        $this->listing->job_type = $data['job_type'];
        $this->listing->address = $data['address'];

        $dateString = trim($data['date']);
        $date = DateTime::createFromFormat('d/m/Y', $dateString);

        if ($date !== false) {
            $formattedDate = $date->format('Y-m-d');
            $this->listing->application_close_date = $formattedDate;
        } else {

            $currentDate = new DateTime();

            // Add one week to the current date
            $currentDate->modify('+1 week');

            // Get the date for next week
            $nextWeek = $currentDate->format('Y-m-d');
            $this->listing->application_close_date = $nextWeek; // Default date
            // Log::error('Failed to parse date: ' . $dateString);
        }


        //$this->listing->application_close_date = DateTime::createFromFormat('d/m/Y', trim($data['date']))->format('Y-m-d');
        $this->listing->salary = $data['salary'];
        $this->listing->slug = Str::slug($data['title']) . '.' . Str::uuid();
        $this->listing->save();
    }


    public function updatePost(int $id, Request $data): void
    {
        if ($data->hasFile('feature_image')) {
            $this->listing->find($id)->update(['feature_image' => $this->getImagePath($data)]);
        }

        $this->listing->find($id)->update($data->except('feature_image'));
    }
}
