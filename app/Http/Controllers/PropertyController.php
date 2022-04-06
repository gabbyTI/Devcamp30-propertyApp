<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Str;

class PropertyController extends Controller
{
    // Adding new property to the database table
    public function createProperty(Request $request){
        // validate request body
        $request->validate([
            'name' => ['required','min:5','unique:properties,name'],
            'state' => ['required'],
            'type' => ['required'],
            'bedrooms' => ['required'],
        ]);

        // add property into the database
        $newProperty = Property::create([
            'user_id' => 1,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'state' => $request->state,
            'type' => $request->type,
            'bedrooms' => $request->bedrooms,
        ]);

        // return success response
        return response()->json([
            'success' => true,
            'message' => 'Successfully created a new property',
            'data' => $newProperty
        ]);
    }

    public function getAllProperties(){}
    public function getProperty(){}
    public function updateProperty(){}
    public function deleteProperty(){}
}
