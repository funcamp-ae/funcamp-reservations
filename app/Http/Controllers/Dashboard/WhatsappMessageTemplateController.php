<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WhatsappMessageTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = WhatsappMessageTemplate::query();

        // Type filter only
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->orderBy('type')->orderBy('created_at', 'desc')->paginate(10);

        // Preserve filters in pagination links
        $templates->appends($request->query());

        return view('dashboard.whatsapp_templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $types = WhatsappMessageTemplate::getTypes();
        $defaultPlaceholders = WhatsappMessageTemplate::getDefaultPlaceholders();
        // Load DB placeholders through the model instance helper
        $dbPlaceholders = app(\App\Models\WhatsappMessageTemplate::class);
        $dbPlaceholders = (new \ReflectionClass($dbPlaceholders))->getMethod('getDatabasePlaceholderAliases')->invoke($dbPlaceholders);
        $variableKeys = $this->getTranslatedVariableKeys();
        return view('dashboard.whatsapp_templates.create', compact('types', 'defaultPlaceholders', 'dbPlaceholders', 'variableKeys'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(WhatsappMessageTemplate::getRawTypes())],
            'message_ar' => 'required|string',
            'message_en' => 'required|string',
            'is_active' => 'nullable|in:on,1,true',
            // 'send_with_pdf' => 'nullable|in:on,1,true',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            WhatsappMessageTemplate::create([
                'name' => $request->name,
                'type' => $request->type,
                'message_ar' => $request->message_ar,
                'message_en' => $request->message_en,
                'is_active' => $request->has('is_active'),
                // 'send_with_pdf' => $request->has('send_with_pdf'),
                'description' => $request->description,
            ]);

            return redirect()->route('whatsapp-templates.index')
                ->with('success', __('dashboard.template_created_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error',$e->getMessage() ?? __('dashboard.something_went_wrong'))
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $template = WhatsappMessageTemplate::findOrFail($id);
        return view('dashboard.whatsapp_templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $template = WhatsappMessageTemplate::findOrFail($id);
        $types = WhatsappMessageTemplate::getTypes();
        $defaultPlaceholders = WhatsappMessageTemplate::getDefaultPlaceholders();
        $dbPlaceholders = app(\App\Models\WhatsappMessageTemplate::class);
        $dbPlaceholders = (new \ReflectionClass($dbPlaceholders))->getMethod('getDatabasePlaceholderAliases')->invoke($dbPlaceholders);
        $variableKeys = $this->getTranslatedVariableKeys();        
        return view('dashboard.whatsapp_templates.edit', compact('template', 'types', 'defaultPlaceholders', 'dbPlaceholders', 'variableKeys'));
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
        $template = WhatsappMessageTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(WhatsappMessageTemplate::getRawTypes())],
            'message_ar' => 'required|string',
            'message_en' => 'required|string',
            'is_active' => 'nullable|in:on,1,true',
            // 'send_with_pdf' => 'nullable|in:on,1,true',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $template->update([
                'name' => $request->name,
                'type' => $request->type,
                'message_ar' => $request->message_ar,
                'message_en' => $request->message_en,
                'is_active' => $request->has('is_active'),
                // 'send_with_pdf' => $request->has('send_with_pdf'),
                'description' => $request->description,
            ]);

            return redirect()->route('whatsapp-templates.index')
                ->with('success', __('dashboard.template_updated_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('dashboard.something_went_wrong'))
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $template = WhatsappMessageTemplate::findOrFail($id);
            $template->delete();

            return redirect()->route('whatsapp-templates.index')
                ->with('success', __('dashboard.template_deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('dashboard.something_went_wrong'));
        }
    }

    /**
     * Toggle template active status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus($id)
    {
        try {
            $template = WhatsappMessageTemplate::findOrFail($id);
            $template->update(['is_active' => !$template->is_active]);

            $status = $template->is_active ? __('dashboard.activated') : __('dashboard.deactivated');
            return response()->json([
                'success' => true,
                'message' => __('dashboard.template_status_updated', ['status' => $status]),
                'is_active' => $template->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('dashboard.something_went_wrong')
            ], 500);
        }
    }

    /**
     * Get all available variable keys with translations for datalist
     *
     * @return array
     */
    private function getTranslatedVariableKeys()
    {
        return [
            // Customer & Order Basic Info
            'customer_id' => __('dashboard.customer_id'),
            'customer_name' => __('dashboard.customer_name'),
            'price' => __('dashboard.price'),
            'deposit' => __('dashboard.deposit'),
            'insurance_amount' => __('dashboard.insurance_amount'),
            'notes' => __('dashboard.notes'),
            'date' => __('dashboard.date'),
            'time_from' => __('dashboard.time_from'),
            'time_to' => __('dashboard.time_to'),
            'people_count' => __('dashboard.people_count'),
            'client_notes' => __('dashboard.client_notes'),
            'status' => __('dashboard.status'),
            
            // Receipt & Delivery Times
            'time_of_receipt' => __('dashboard.time_of_receipt'),
            'time_of_receipt_notes' => __('dashboard.time_of_receipt_notes'),
            'delivery_time' => __('dashboard.delivery_time'),
            'delivery_time_notes' => __('dashboard.delivery_time_notes'),
            'delayed_time' => __('dashboard.delayed_time'),
            
            // Media Notes
            'voice_note' => __('dashboard.voice_note'),
            'video_note' => __('dashboard.video_note'),
            'voice_note_logout' => __('dashboard.voice_note_logout'),
            'video_note_logout' => __('dashboard.video_note_logout'),
            
            // Images
            'image_before_receiving' => __('dashboard.image_before_receiving'),
            'image_after_delivery' => __('dashboard.image_after_delivery'),
            'guidance_images' => __('dashboard.guidance_images'),
            'guidance_videos' => __('dashboard.guidance_videos'),
            
            // Refunds & Insurance
            'refunds_notes' => __('dashboard.refunds_notes'),
            'insurance_status' => __('dashboard.insurance_status'),
            'partial_confiscation_amount' => __('dashboard.partial_confiscation_amount'),
            'confiscation_description' => __('dashboard.confiscation_description'),
                        
            // Document Notes
            'show_price_notes' => __('dashboard.show_price_notes'),
            'order_data_notes' => __('dashboard.order_data_notes'),
            'invoice_notes' => __('dashboard.invoice_notes'),
            'receipt_notes' => __('dashboard.receipt_notes'),
            
            // Signature & Evaluation
            'signature_path' => __('dashboard.signature_path'),
            'signature' => __('dashboard.signature'),
            'evaluation_link' => __('dashboard.evaluation_link'),
            
            // Service Site Information
            'service_site' => __('dashboard.service_site'),
            'service_location' => __('dashboard.service_location'),
            'workername_ar' => __('dashboard.worker_name_ar'),
            'workername_en' => __('dashboard.worker_name_en'),
            'workerphone' => __('dashboard.worker_phone'),
            'important_notes' => __('dashboard.important_notes'),
            
            // Terms & Conditions
            'terms_conditions_link' => __('dashboard.terms_conditions_link'),
        ];
    }
}
