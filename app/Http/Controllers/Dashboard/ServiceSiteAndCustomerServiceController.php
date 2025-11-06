<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceSiteAndCustomerService;

class ServiceSiteAndCustomerServiceController extends Controller
{

    public function create()
    {
        $items = ServiceSiteAndCustomerService::all();
        return view('dashboard.service_site_customer_service.create', compact('items'));
    }
    public function edit($id)
    {
        $item = ServiceSiteAndCustomerService::findOrFail($id);
        return view('dashboard.service_site_customer_service.create', compact('item'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Debug: Log incoming request data
        \Log::info('Store request data:', [
            'all_data' => $request->all(),
            'guidance_videos' => $request->input('guidance_videos'),
            'has_guidance_videos' => $request->has('guidance_videos')
        ]);

        $validatedData = $request->validate([
            'serviceSite' => 'required|string',
            'workername_en' => 'required|string',
            'workername_ar' => 'required|string',
            'workerphone' => 'required|string',
            'guidance_images' => 'nullable|array',
            'guidance_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'guidance_videos' => 'nullable|array',
            'guidance_videos.*' => 'nullable|url',
            'important_notes' => 'nullable|string',
        ]);

        $data = [
            'service_site' => $validatedData['serviceSite'],
            'workername_en' => $validatedData['workername_en'],
            'workername_ar' => $validatedData['workername_ar'],
            'workerphone' => $validatedData['workerphone'],
            'important_notes' => $request->input('important_notes'),
        ];

        // Handle multiple image uploads
        if ($request->hasFile('guidance_images')) {
            $imagePaths = [];
            foreach ($request->file('guidance_images') as $image) {
                if ($image && $image->isValid()) {
                    $path = $image->store('guidance_images', 'public');
                    $imagePaths[] = $path;
                }
            }
            $data['guidance_images'] = !empty($imagePaths) ? $imagePaths : null;
        }

        // Handle video links - filter out empty values
        if ($request->has('guidance_videos')) {
            $videoLinks = array_filter($request->input('guidance_videos', []), function($link) {
                return !empty($link);
            });
            $data['guidance_videos'] = !empty($videoLinks) ? array_values($videoLinks) : null;
            
            // Debug: Log processed video links
            \Log::info('Processed video links:', [
                'video_links' => $videoLinks,
                'final_data' => $data['guidance_videos']
            ]);
        }

        $record = ServiceSiteAndCustomerService::create($data);
        
        // Debug: Log created record
        \Log::info('Created record:', [
            'id' => $record->id,
            'guidance_videos' => $record->guidance_videos
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
            'success' => true,
            'message' => __('item_added_successfully'),

            'data' => $validatedData,
            ], 201);
        }

        return redirect()->route('bookings.service-site-customer-service.create')->with('success',  __('item_added_successfully'));
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
        $validatedData = $request->validate([
            'serviceSite' => 'required|string',
            'workername_en' => 'required|string',
            'workername_ar' => 'required|string',
            'workerphone' => 'required|string',
            'guidance_images' => 'nullable|array',
            'guidance_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'new_guidance_images' => 'nullable|array',
            'new_guidance_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_guidance_images' => 'nullable|array',
            'deleted_guidance_images' => 'nullable|array',
            'guidance_videos' => 'nullable|array',
            'guidance_videos.*' => 'nullable|url',
            'important_notes' => 'nullable|string',
        ]);

        $item = ServiceSiteAndCustomerService::findOrFail($id);
        
        $data = [
            'service_site' => $validatedData['serviceSite'],
            'workername_en' => $validatedData['workername_en'],
            'workername_ar' => $validatedData['workername_ar'],
            'workerphone' => $validatedData['workerphone'],
            'important_notes' => $request->input('important_notes'),
        ];

        // Handle image updates
        $finalImages = [];
        
        // Keep existing images that weren't deleted
        if ($request->has('existing_guidance_images')) {
            $existingImages = $request->input('existing_guidance_images', []);
            $deletedImages = $request->input('deleted_guidance_images', []);
            
            foreach ($existingImages as $existingImage) {
                if (!in_array($existingImage, $deletedImages)) {
                    $finalImages[] = $existingImage;
                }
            }
            
            // Delete removed images from storage
            foreach ($deletedImages as $deletedImage) {
                \Storage::disk('public')->delete($deletedImage);
            }
        }
        
        // Add new uploaded images
        if ($request->hasFile('new_guidance_images')) {
            foreach ($request->file('new_guidance_images') as $image) {
                if ($image && $image->isValid()) {
                    $path = $image->store('guidance_images', 'public');
                    $finalImages[] = $path;
                }
            }
        }
        
        // Add brand new images (from "Add Image" button)
        if ($request->hasFile('guidance_images')) {
            foreach ($request->file('guidance_images') as $image) {
                if ($image && $image->isValid()) {
                    $path = $image->store('guidance_images', 'public');
                    $finalImages[] = $path;
                }
            }
        }
        
        $data['guidance_images'] = !empty($finalImages) ? $finalImages : null;

        // Handle video links - filter out empty values
        if ($request->has('guidance_videos')) {
            $videoLinks = array_filter($request->input('guidance_videos', []), function($link) {
                return !empty($link);
            });
            $data['guidance_videos'] = !empty($videoLinks) ? array_values($videoLinks) : null;
        }

        $item->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
            'success' => true,
            'message' => __('item_updated_successfully'),
            ]);
        }

        return redirect()->route('bookings.service-site-customer-service.create', $item->id)->with('success', __('item_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item = ServiceSiteAndCustomerService::findOrFail($id);
        $item->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
            'success' => true,
            'message' => __('dashboard.item_deleted_successfully'),
            ]);
        }

        return redirect()->route('bookings.service-site-customer-service.create')->with('success', __('dashboard.item_deleted_successfully'));
    }
}
