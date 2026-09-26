@extends('layouts.admin')

@section('title', 'Destinations')

@section('content')

    <x-admin.page-header
        title="Manage Destinations"
        description="Destination content shown on the customer website."
        eyebrow="Website content"
        icon="D"
    >
        <a class="egh-button secondary" href="{{ route('home') }}">
            View website
        </a>
    </x-admin.page-header>

    <div class="egh-card">
        <div class="admin-card-heading">
            <div>
                <span class="admin-page-eyebrow">Not stored yet</span>
                <h2>Destinations are not stored by this application</h2>
                <p>
                    This website does not have a destination catalogue, image
                    storage or publishing workflow, so no destination, image,
                    translation or status can be created, edited or published
                    from here. This page reports that instead of offering
                    controls that cannot save anything.
                </p>
            </div>
        </div>

        <p class="egha-chart-note">
            No destination record, image upload or status change is written
            anywhere by this page.
        </p>

        <div class="egha-actions">
            <a class="egh-button secondary" href="{{ route('admin.master-data.manage') }}">
                Open master data
            </a>
            <a class="egh-button secondary" href="{{ route('admin.reports.index') }}">
                Open reports
            </a>
        </div>
    </div>

    {{--
        Layout preview only, local development only.

        The toolbar and the rows below show the approved management table
        layout. They are static samples, every control is disabled, and none of
        the images are uploaded files.

        Wiring point: a destination model with image storage, ordering and a
        publish state supplies the rows, and the row actions become real
        create, edit and publish endpoints.
    --}}
    @if (app()->environment('local'))
        <div class="egha-note">
            <span aria-hidden="true">&#9432;</span>
            <span>
                <strong>Layout preview.</strong>
                Sample rows and disabled controls that preview the management
                table. Nothing here is stored or published.
            </span>
        </div>

        <div class="egh-card">
            <div class="egha-toolbar">
                <div class="egha-search">
                    <label>
                        <span>Search destinations</span>
                        <input type="search" placeholder="Sample search" disabled>
                    </label>

                    <button type="button" class="egh-button secondary" disabled>
                        Search
                    </button>
                </div>

                <button type="button" class="egh-button primary" disabled>
                    Add new destination
                </button>
            </div>

            <div class="egha-table-wrap">
                <table class="egha-table">
                    <thead>
                        <tr>
                            <th scope="col">Image</th>
                            <th scope="col">Name</th>
                            <th scope="col">Country</th>
                            <th scope="col">State</th>
                            <th scope="col">City</th>
                            <th scope="col">Status</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ([
                            [
                                'name' => 'Sample destination one',
                                'country' => 'Sample country',
                                'state' => 'Sample state',
                                'city' => 'Sample city',
                                'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=400&q=85',
                                'published' => true,
                            ],
                            [
                                'name' => 'Sample destination two',
                                'country' => 'Sample country',
                                'state' => 'Sample state',
                                'city' => 'Sample city',
                                'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=400&q=85',
                                'published' => true,
                            ],
                            [
                                'name' => 'Sample destination three',
                                'country' => 'Sample country',
                                'state' => 'Sample state',
                                'city' => 'Sample city',
                                'image' => 'https://images.unsplash.com/photo-1506973035872-a4ec16b8e8d9?auto=format&fit=crop&w=400&q=85',
                                'published' => false,
                            ],
                        ] as $destination)
                            <tr>
                                <td>
                                    <img
                                        class="egha-thumb"
                                        src="{{ $destination['image'] }}"
                                        alt=""
                                        loading="lazy"
                                        width="400"
                                        height="266"
                                    >
                                </td>
                                <td>
                                    <span class="egha-cell-title">
                                        {{ $destination['name'] }}
                                    </span>
                                    <span class="egha-cell-sub">
                                        {{ $destination['city'] }}
                                    </span>
                                </td>
                                <td class="egha-muted">
                                    {{ $destination['country'] }}
                                </td>
                                <td class="egha-muted">
                                    {{ $destination['state'] }}
                                </td>
                                <td class="egha-muted">
                                    {{ $destination['city'] }}
                                </td>
                                <td>
                                    {{--
                                        The switch mirrors the published state.
                                        It is disabled because no publish action
                                        exists: nothing here writes a record.
                                    --}}
                                    <span
                                        @class([
                                            'egha-switch',
                                            'egha-switch-on' => $destination['published'],
                                        ])
                                    >
                                        <span
                                            class="egha-switch-track"
                                            aria-hidden="true"
                                        ></span>
                                        {{ $destination['published'] ? 'Published' : 'Hidden' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="egha-row-actions">
                                        <button
                                            type="button"
                                            class="egha-icon-btn is-edit"
                                            disabled
                                            aria-label="Edit sample destination"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 20h4L18 10l-4-4L4 16z"/>
                                                <path d="m14 6 4 4"/>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            class="egha-icon-btn is-delete"
                                            disabled
                                            aria-label="Delete sample destination"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 7h16M9 7V5h6v2M6 7l1 12h10l1-12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="egha-chart-note">
                Sample rows only. Real rows appear once destination storage is
                implemented and connected.
            </p>
        </div>
    @endif

@endsection
