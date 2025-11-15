<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\WhatsappMessageType;
use App\Models\WhatsappPlaceholder;
use App\Models\WhatsappMessageTemplate;

class WhatsappMetaController extends Controller
{
    public function storeType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:191|unique:whatsapp_message_types,key',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        WhatsappMessageType::create(['key' => $request->key]);

        return response()->json([
            'success' => true,
            'key' => $request->key,
            'label' => __("dashboard.".$request->key),
            'types' => WhatsappMessageTemplate::getTypes(),
        ]);
    }

    public function storePlaceholder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'placeholder' => 'required|string|max:191|starts_with:[',
            'variable_key' => 'required|string|max:191',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $row = WhatsappPlaceholder::updateOrCreate(
            ['placeholder' => $request->placeholder],
            ['variable_key' => $request->variable_key]
        );

        return response()->json([
            'success' => true,
            'placeholder' => $row->placeholder,
            'variable_key' => $row->variable_key,
        ]);
    }
}
