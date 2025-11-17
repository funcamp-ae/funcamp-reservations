<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceReport;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServicesController extends Controller
{
    // Display a listing of services
    public function index()
    {
        // Allow access if user has either services.index OR camp-types.index permission
        if (!auth()->user()->can('services.index') && !auth()->user()->can('camp-types.index')) {
            abort(403, 'Unauthorized');
        }
        
        $services = Service::orderBy('created_at', 'desc')->get();
        return view('dashboard.services.index', compact('services'));
    }

    // Show the form for creating a new service
    public function create()
    {
        // Allow access if user has either services.create OR camp-types.create permission
        if (!auth()->user()->can('services.create') && !auth()->user()->can('camp-types.create')) {
            abort(403, 'Unauthorized');
        }
        
        $stocks = Stock::latest()->get();
        return view('dashboard.services.create', compact('stocks'));
    }

    // Store a newly created service in the database
    public function store(Request $request)
    {
        \Log::info($request->all());
        // Allow access if user has either services.create OR camp-types.create permission
        if (!auth()->user()->can('services.create') && !auth()->user()->can('camp-types.create')) {
            abort(403, 'Unauthorized');
        }

        try {
            $validatedData = $request->validate([
                'name' => 'required|max:255',
                'description' => 'nullable',
                'price' => 'required|numeric',
                'hours' => 'required|integer',
                'hour_from' => 'required',
                'hour_to' => 'required',
                'stocks' => 'nullable|array',
                'stocks.*' => 'nullable|distinct|min:1',
                'counts' => 'nullable|array',
                'counts.*' => 'nullable|integer|min:1',
                'reports' => 'nullable|array',
                'reports.*' => 'required|string|max:255',
                'reports_counts' => 'nullable|array',
                'reports_counts.*' => 'required|integer|min:1',
                'reports_images' => 'nullable|array',
                'reports_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
                'report_orders' => 'nullable|array',
                'report_orders.*' => 'nullable|integer',
                'set_qty' => 'nullable|integer'
            ]);

            \DB::beginTransaction();
            $request['registeration_forms'] = $request['registeration_forms'] == 'on' ? 1 : 0;
            $serviceData = $request->only(['name', 'description', 'price', 'hours', 'hour_from', 'hour_to','registeration_forms']);
            $service = Service::create($serviceData);
            $report_orders = $request->report_orders ?? [];
            if (isset($request->stocks) && isset($request->counts)) {
                $stocksData = array_combine($request->stocks, $request->counts);
                $service->stocks()->sync([]);

                foreach ($stocksData as $stockId => $count) {
                    $service->stocks()->attach($stockId, ['count' => $count]);
                }
            }
            if (isset($request->reports) && isset($request->reports_counts)) {
                foreach ($request->reports as $index => $reportName) {
                    // \Log::info($reportName);
                    $count = $request->reports_counts[$index]; // the count for each report record
                    $order = $request->report_orders[$index] ?? $index; // the order for each report record
                    $newReport = $service->reports()->create([
                        'name' => $reportName,
                        'count' => $count,
                        'report_orders' => $order,
                        'set_qty' => 0
                    ]);
                    $imagePath = null;
                    
                    // Check for regular file upload first. Prefer the loop $index as the key (matches inputs),
                    // but fall back to the provided order value if present (defensive).
                    $files = $request->file('reports_images')[$report_orders[$index] - 1] ?? $request->file('reports_images')[$report_orders[$index] - 1] ?? null;
                    if ($files instanceof \Illuminate\Http\UploadedFile) {
                        // \Log::info("yes there is reports image");
                        $imagePath = $files->store('reports', 'public');
                    }

                    // Handle custom_reports_images (from retrieved service)
                    else if (isset($request->custom_reports_images) && array_key_exists($report_orders[$index] - 1, $request->custom_reports_images)) {
                        $existingPath = $request->custom_reports_images[$report_orders[$index] - 1];
                        // \Log::info("yes there is custom reports image");
                        // \Log::info($existingPath);

                        // Normalize the provided URL/path to a storage-relative path.
                        // Use parse_url to extract the path component, then remove leading '/storage/' or 'storage/'.
                        $path = $existingPath;
                        $urlPath = parse_url($path, PHP_URL_PATH) ?: $path;
                        $urlPath = ltrim($urlPath, '/');
                        if (str_starts_with($urlPath, 'storage/')) {
                            $existingPath = preg_replace('#^storage/#', '', $urlPath);
                        } else {
                            $existingPath = $urlPath;
                        }
                        // \Log::info($existingPath);

                        // Check if the file exists in public storage
                        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                            // \Log::info("file exists in public storage: " . $existingPath);
                            $imagePath = $existingPath;
                        }
                    }
                    // \Log::info("image path set to: " . $imagePath);
                    // Update the report with the image path if we have one
                    if ($imagePath) {
                        // \Log::info("updating report image with path: " . $imagePath);
                        $newReport->update(['image' => $imagePath]);
                    }
                    // dd("");
                }
            }
            \DB::commit();
            \Log::info($request->all());
            return response()->json([
                'message' => __('dashboard.service_created_successfully'),
                'redirect' => route('services.index')
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'errors' => ['server' => $e->getMessage()]
            ], 500);
        }
    }

    // Display the specified service
    public function show(Service $service)
    {
        $this->authorize('view', $service);
        return view('dashboard.services.show', compact('service'));
    }

    // Show the form for editing the specified service
    public function edit($service)
    {
        $service = Service::findOrFail($service);
        // Allow access if user has either services.edit OR camp-types.edit permission
        if (!auth()->user()->can('services.edit') && !auth()->user()->can('camp-types.edit')) {
            abort(403, 'Unauthorized');
        }
        
        $stocks = Stock::all();
        $reports = ServiceReport::where('service_id', $service->id)
                                    ->orderBy('ordered_count')
                                    ->get();

        return view('dashboard.services.create', compact('service', 'stocks', 'reports'));
    }
    public function retrieveCamp(Service $service)
    {
        $retrievedService = $service;
        $stocks = Stock::all();
        $reports = ServiceReport::where('service_id', $service->id)
        ->orderBy('ordered_count')
        ->get();
        return view('dashboard.services.create', compact('retrievedService', 'stocks', 'reports'));

    }
    public function update(Request $request, $serviceId)
    {
        $service = Service::findOrFail($serviceId);
        $this->authorize('update', $service);

        $validated = $request->validate([
            'name'           => 'required|max:255',
            'description'    => 'nullable',
            'price'          => 'required|numeric',
            'hours'          => 'required|integer',
            'hour_from'      => 'required',
            'registeration_forms' => 'nullable',
            'hour_to'        => 'required',
            'stocks'         => 'nullable|array',
            'stocks.*'       => 'nullable|distinct|exists:stocks,id',
            'counts'         => 'nullable|array',
            'counts.*'       => 'nullable|integer|min:1',
            'report_orders'  => 'nullable|array',
            'report_orders.*'=> 'nullable|integer',
            'images'         => 'nullable|array',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2040',
            'set_qty'       => 'nullable|integer'
        ]);

        $requestedCounts = collect($validated['stocks'] ?? [])
            ->mapWithKeys(function ($id, $idx) use ($validated) {
                $sid = (int) $id;
                $cnt = (int) ($validated['counts'][$idx] ?? 0);
                return $sid ? [$sid => $cnt] : [];
            })
            ->filter(fn ($c) => $c > 0)
            ->reduce(function ($carry, $count, $stockId) {
                $carry[$stockId] = ($carry[$stockId] ?? 0) + $count;
                return $carry;
            }, []);

       \Log::info('Update request received', ['request' => $request->all()]);
        \Log::info('Validated data', ['validated' => $validated]);
        \Log::info('Requested counts', ['requestedCounts' => $requestedCounts]);

        try {
            \Log::info('Starting transaction for service update', ['serviceId' => $serviceId]);
            \DB::beginTransaction();
            \Log::info('Fetching existing pivot data');
            $existingPivot = $service->stocks()
                ->get()
                ->mapWithKeys(fn ($s) => [(int)$s->id => (int)$s->pivot->count]);
            \Log::info('Existing pivot data', ['existingPivot' => $existingPivot]);

            $existingPivotKeys = $existingPivot->keys()->all();

            \Log::info('Calculating lock IDs');
            $lockIds = array_values(array_unique(array_merge(
                array_keys($requestedCounts),
                $existingPivotKeys
            )));
            \Log::info('Lock IDs', ['lockIds' => $lockIds]);

            \Log::info('Fetching stock models');
            $stockModels = Stock::whereIn('id', $lockIds)->lockForUpdate()->get()->keyBy('id');
            \Log::info('Stock models fetched', ['stockModels' => $stockModels]);

            foreach ($requestedCounts as $stockId => $newCount) {
                \Log::info('Processing stock', ['stockId' => $stockId, 'newCount' => $newCount]);
                $stock = $stockModels[$stockId] ?? null;
                if (!$stock) {
                    \Log::error('Stock not found', ['stockId' => $stockId]);
                    throw new \RuntimeException("Stock #{$stockId} not found.");
                }
                $old   = $existingPivot[$stockId] ?? 0;
                $delta = $newCount - $old;
                \Log::info('Stock delta calculated', ['stockId' => $stockId, 'delta' => $delta]);
                if ($delta > 0 && (int)$stock->quantity < $delta) {
                    \Log::error('Insufficient stock quantity', ['stockId' => $stockId, 'delta' => $delta, 'available' => $stock->quantity]);
                    throw ValidationException::withMessages([
                        'stocks' => ["Insufficient quantity for '{$stock->name}'. Need {$delta}, available {$stock->quantity}."],
                    ]);
                }
            }

            \Log::info('Preparing sync payload');
            $syncPayload = [];
            foreach ($requestedCounts as $stockId => $count) {
                $syncPayload[$stockId] = ['count' => $count];
            }
            \Log::info('Sync payload prepared', ['syncPayload' => $syncPayload]);

            \Log::info('Syncing stocks');
            $service->stocks()->sync($syncPayload);
            \Log::info('Stocks synced successfully');

            \Log::info('Updating service details');
            $service->update([
                'name'        => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price'       => $validated['price'],
                'hours'       => $validated['hours'],
                'registeration_forms' => isset($validated['registeration_forms']) ? ($validated['registeration_forms'] == 'on' ? 1 : 0) : 0,
                'hour_from'   => $validated['hour_from'],
                'hour_to'     => $validated['hour_to'],
            ]);
            \Log::info('Service details updated successfully');

            if ($request->filled('reports') && $request->filled('reports_counts')) {
                \Log::info('Processing reports');
                $existingReports = $service->reports()->pluck('id', 'id')->toArray();
                \Log::info('Existing reports fetched', ['existingReports' => $existingReports]);
                $updatedReports  = [];

                foreach ($request->reports as $index => $reportName) {
                    \Log::info('Processing report', ['index' => $index, 'reportName' => $reportName]);
                    $count    = (int) ($request->reports_counts[$index] ?? 0);
                    $reportId = $request->report_ids[$index] ?? null;
                    $order    = $request->report_orders[$index];
                    $reportData = [
                        'name'       => $reportName,
                        'count'      => $count,
                        'service_id' => $service->id,
                        'set_qty'    => 0
                    ];

                    if ($reportId && isset($existingReports[$reportId])) {
                        \Log::info('Updating existing report', ['reportId' => $reportId]);
                        $report = ServiceReport::findOrFail($reportId);
                        $report->update($reportData);

                        $file = $request->file('reports_images')[$request->report_orders[$index] - 1] ?? $request->file('reports_images')[$order] ?? null;
                            \Log::info('Report image file', ['file' => $file]);
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            \Log::info('Uploading new report image', ['reportId' => $reportId]);
                            if ($report->image && Storage::disk('public')->exists($report->image)) {
                                Storage::disk('public')->delete($report->image);
                            }
                            $path = $file->store('reports', 'public');
                            $report->update(['image' => $path]);
                            \Log::info('Report image updated', ['reportId' => $reportId, 'path' => $path]);
                        }

                        $updatedReports[] = $reportId;
                        unset($existingReports[$reportId]);
                    } else {
                        \Log::info('Creating new report', ['reportName' => $reportName]);
                        $file = $request->file('reports_images')[$request->report_orders[$index] - 1] ?? $request->file('reports_images')[$order] ?? null;
                        if ($file instanceof \Illuminate\Http\UploadedFile) {
                            $reportData['image'] = $file->store('reports', 'public');
                        }
                        $newReport = ServiceReport::create($reportData);
                        $updatedReports[] = $newReport->id;
                    }
                }

                if (!empty($existingReports)) {
                    \Log::info('Deleting unused reports', ['reportIds' => array_keys($existingReports)]);
                    ServiceReport::whereIn('id', array_keys($existingReports))->delete();
                }
            } else {
                \Log::info('Deleting all reports for service', ['serviceId' => $service->id]);
                $service->reports()->delete();
            }

            \DB::commit();
            \Log::info('Service update transaction committed successfully');
            return response()->json();
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error('Service update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'errors' => ['server' => $e->getMessage()],
            ], 500);
        }
    }

    // Remove the specified service from the database
    public function destroy($service)
    {
        $service = Service::findOrFail($service);
        $this->authorize('delete', $service);
        $service->delete();
        return response()->json();
    }

    public function deleteAll(Request $request)
    {
        $this->authorize('delete', Service::class);

        $requestIds = json_decode($request->data);

        foreach ($requestIds as $id) {
            $ids[] = $id->id;
        }
        if (Service::whereIn('id', $ids)->delete()) {
            return response()->json('success');
        } else {
            return response()->json('failed');
        }
    }

    public function move(Request $request, Service $service, ServiceReport $report)
    {
        $this->authorize('move', Service::class);

        $data = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        return \DB::transaction(function () use ($service, $report, $data) {
            $this->normalizeOrderedCounts($service);
            $current = ServiceReport::where('service_id', $service->id)
                ->where('id', $report->id)
                ->lockForUpdate()
                ->firstOrFail();

            $targetOrder = $data['direction'] === 'up' ? $current->ordered_count - 1 : $current->ordered_count + 1;

            if ($targetOrder < 1) {
                return response()->json(['message' => 'noop', 'moved' => false], 200);
            }

            $max = (int) ServiceReport::where('service_id', $service->id)->max('ordered_count');
            if ($targetOrder > $max) {
                return response()->json(['message' => 'noop', 'moved' => false], 200);
            }

            $neighbor = ServiceReport::where('service_id', $service->id)
                ->where('ordered_count', $targetOrder)
                ->lockForUpdate()
                ->first();

            if (!$neighbor) {
                $this->normalizeOrderedCounts($service);
                $neighbor = ServiceReport::where('service_id', $service->id)
                    ->where('ordered_count', $targetOrder)
                    ->lockForUpdate()
                    ->first();

                if (!$neighbor) {
                    return response()->json(['message' => 'noop', 'moved' => false], 200);
                }
            }

            // Properly swap the ordered_count values
            $tempOrder = $current->ordered_count;
            $current->ordered_count = $neighbor->ordered_count;
            $neighbor->ordered_count = $tempOrder;

            $current->save();
            $neighbor->save();

            return response()->json([
                'message' => 'ok',
                'moved'   => true,
                'current' => ['id' => $current->id, 'ordered_count' => $current->ordered_count],
                'neighbor'=> ['id' => $neighbor->id, 'ordered_count' => $neighbor->ordered_count],
            ], 200);
        });
    }
    private function normalizeOrderedCounts(Service $service): void
    {
        $rows = ServiceReport::where('service_id', $service->id)
            ->orderByRaw('CASE WHEN ordered_count IS NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('ordered_count')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $i = 1;
        foreach ($rows as $r) {
            if ((int)$r->ordered_count !== $i) {
                $r->ordered_count = $i;
                $r->save();
            }
            $i++;
        }
    }

}
