<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyResource;
use App\Jobs\UploadImage;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Str;

class PropertyController extends Controller
{
    // Adding new property to the database table
    public function createProperty(Request $request)
    {
        // validate request body
        $request->validate([
            'name' => ['required', 'min:5', 'unique:properties,name'],
            'state' => ['required'],
            'type' => ['required', 'in:buy,rent,shortlet'],
            'bedrooms' => ['required'],
            'price_per_anum' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'image' => ['mimes:png,jpeg,gif,bmp', 'max:2048'],

        ]);
        //get the image
        $image = $request->file('image');

        // get original file name and replace any spaces with _
        // example: ofiice card.png = timestamp()_office_card.pnp
        $filename = time() . "_" . preg_replace('/\s+/', '_', strtolower($image->getClientOriginalName()));

        // move image to temp location (tmp disk)
        $tmp = $image->storeAs('uploads/original', $filename, 'tmp');

        // add property into the database
        $newProperty = Property::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'state' => $request->state,
            'type' => $request->type,
            'bedrooms' => $request->bedrooms,
            'image' => $filename,
            'disk' => config('site.upload_disk'),
            'price_per_anum' => $request->price_per_anum,
            'address' => $request->address,
        ]);

        //dispacth job to handle image manipulation
        $this->dispatch(new UploadImage($newProperty));


        // return success response
        return response()->json([
            'success' => true,
            'message' => 'Successfully created a new property',
            'data' => new PropertyResource($newProperty),
        ]);
    }

    public function getAllProperties()
    {
        $allProperties = Property::all();
        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($allProperties)
        ]);
    }

    public function getProperty(Request $request, $propertyId)
    {
        $property = Property::find($propertyId);
        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found database',
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Property found successfully',
            'data' => [
                'property' => new PropertyResource($property),
            ]

        ]);
    }

    public function updateProperty(Request $request, Property $property)
    {
        // dd($property);
        $request->validate([
            'name' => ['required', 'min:5', 'unique:properties,name,' . $property->id],
            'state' => ['required'],
            'type' => ['required'],
            'bedrooms' => ['required']
        ]);

        $this->authorize('update', $property);

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

    public function deleteProperty($propertyId)
    {
        $property = Property::find($propertyId);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found database',
            ]);
        }

        //Deleting the images associated with the property
        foreach (['thumbnail', 'large', 'original'] as $size) {
            //check if file exist
            if (Storage::disk($property->disk)->exists("uploads/properties/{$size}/" . $property->image)) {
                Storage::disk($property->disk)->delete("uploads/properties/{$size}/" . $property->image);
            }
        }

        // delete property
        $property->delete();

        // return succcess response
        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully'
        ]);
    }

    public function search(Request $request)
    {
        $property = new Property();
        $query = $property->newQuery();

        if ($request->has('state')) {
            $query->where('state', $request->state);
        }

        if ($request->has('type')) {

            // dd($request->type);
            $query->where('type', '=', $request->type);
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        if ($request->has('minPrice')) {
            $query->where('price_per_anum', '>=', $request->minPrice);
        }

        if ($request->has('maxPrice')) {
            $query->where('price_per_anum', '<=', $request->maxPrice);
        }

        return response()->json([
            'success' => true,
            'message' => 'Search results found',
            'data' => $query->get()
        ]);
    }
}
