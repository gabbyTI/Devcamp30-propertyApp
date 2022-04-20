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
            'price_per_anum' => ['required','integer'],
            'address' => ['required','string'],
        ]);
        // add property into the database
        $newProperty = Property::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'state' => $request->state,
            'type' => $request->type,
            'bedrooms' => $request->bedrooms,
            'price_per_anum' => $request->price_per_anum,
            'address' => $request->address,
        ]);

        // return success response
        return response()->json([
            'success' => true,
            'message' => 'Successfully created a new property',
            'data' => $newProperty
        ]);
    }

    public function getAllProperties() {
        $allProperties = Property::all();
        return response()->json([
            'success' => true,
            'data' => $allProperties
        ]);
    }

    public function getProperty(Request $request, $propertyId) {
        $property = Property::find($propertyId);
        if(!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found database',
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Property found successfully',
            'data' => $property
        ]);
    }

    public function updateProperty(Request $request, $propertyId) {
        $request->validate([
            'name' => ['required','min:5','unique:properties,name,'. $propertyId],
            'state' => ['required'],
            'type' => ['required'],
            'bedrooms' => ['required']
        ]);
        // add updated property info to database table
            $property = Property::find($propertyId);

            if(!$property) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found database',
                ]);
            }

            $property->name = $request->name;
            $property->slug = Str::slug($request->name);
            $property->state = $request->state;
            $property->type = $request->type;
            $property->bedrooms = $request->bedrooms;
            $property->save();
        
        // return succcess response
        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully'
        ]);
    }

    public function deleteProperty($propertyId) {
        $property = Property::find($propertyId);

        if(!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found database',
            ]);
        }
        // delete property
        $property->delete();

        // return succcess response
        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully'
        ]);

    }

    public function search(Request $request){
        $property = new Property();
        $query = $property->newQuery();

        if($request->has('state')){
            $query->where('state', $request->state);
        }

        if($request->has('type')){

            // dd($request->type);
            $query->where('type','=' ,$request->type);
        }

        if($request->has('bedrooms')){
            $query->where('bedrooms', $request->bedrooms);
        }
        
        if ($request->has('minPrice')){
            $query->where('price_per_anum', '>=', $request->minPrice);
        }

        if ($request->has('maxPrice')){
            $query->where('price_per_anum', '<=', $request->maxPrice);
        }

        return response()->json([
            'success' => true,
            'message' => 'Search results found',
            'data' => $query->get()
        ]);

    }

}
