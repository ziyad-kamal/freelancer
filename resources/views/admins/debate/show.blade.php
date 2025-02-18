@extends('adminlte::page')

@section('content')
    <div class="container">
        @if (Session::has('success'))
            <div class="alert alert-success text-center">
                {{ Session::get('success') }}
            </div>
        @endif

        {{-- <a class="btn btn-primary m-1" href="{{ route('admin.edit', $admin->id) }}" 
            role="button">
            edit 
        </a> --}}

        <!-- debate details -->
        <div class="card-body" style="margin-top: 25px">
            <a href="{{ route('admin.project.show', $debate->slug) }}">
                <h5 class="card-title" style="margin-right: 15px">{{ $debate->title }}</h5>
            </a>

            <div class="text-muted" style="margin-bottom: 15px">
                <span>milestone : ${{ $debate->amount }}</span>
                <span style="margin-left: 10px">time: {{ $debate->num_of_days }} days</span>
                <span style="margin-left: 10px">posted:
                    {{ \Carbon\Carbon::parse($debate->created_at)->diffForhumans() }}</span>
            </div>

            <p class="card-text ">project content : {{ $debate->content }}</p>

            <hr>

            <p class="card-text ">debate description : {{ $debate->description }}</p>

            <div class="text-muted" style="margin-top: 10px">
                <span style="margin-left: 10px">status: {{ $debate->status }}</span>

                <a href="{{ route('admin.user.show', $debate->initiator_slug) }}">
                    <span style="margin-left: 10px">initiator: {{ $debate->initiator_name }}</span>

                </a>

                <a href="{{ route('admin.user.show', $debate->opponent_slug) }}">
                    <span style="margin-left: 10px">opponent: {{ $debate->opponent_name }}</span>

                </a>
            </div>
        </div>
    @endsection
