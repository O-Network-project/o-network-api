<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Resources\OrganizationResource;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Http\Requests\ValidateOrganizationRequest;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // In that app MVP, no user with any role can access the list of all
        // organizations
        return response(null, 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrganizationRequest $request)
    {
        return new OrganizationResource(Organization::create($request->all()));
    }

    /**
     * Validate the organization without storing it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check(ValidateOrganizationRequest $request)
    {
        $name = $request->input('name');
        $conflicts = Organization::where('name', $name)->exists();

        if ($conflicts)
            return response()->json(['message' => "The organization '$name' already exists."], 409);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        return new OrganizationResource($organization);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $organization->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();
    }
}
