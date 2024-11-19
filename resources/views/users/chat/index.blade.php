@extends('layouts.app')

@section('header')
    <link rel="stylesheet" href="{{ asset('css/chat/index.css') }}" type="text/css" />

    <script defer src="{{ asset('js/general.js') }}?v={{ filemtime(public_path('js/general.js')) }}"></script>
    <script defer src="{{ asset('js/chat/index.js') }}?v={{ filemtime(public_path('js/chat/index.js')) }}"></script>

    <title>
        {{ 'Chat - ' . config('app.name') }}
    </title>

    <meta name="keywords" content="chat page contain all all_chat_rooms from your friends ">
@endsection

@section('content')
    @if (Session::has('error'))
        <div class="alert alert-danger text-center">{{ Session::get('error') }}</div>
    @endif

    <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-friends" role="tabpanel" aria-labelledby="nav-friends-tab">

                <div class="container">

                    <div class="row" style="margin-top: 50px">
                        <div class="col-4 ">
                            <input type="hidden" value="{{ Auth::user()->name }}" id="auth_name">
                            <input type="hidden" value="{{ Auth::user()->image }}" id="auth_photo">
                            <input type="hidden" value="{{ Auth::id() }}" id="auth_id">

                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">search</span>
                                <input type="text" class="form-control search_input" name="search"
                                    data-search_url="{{ route('search.Chatrooms') }}"placeholder="friend name"
                                    aria-label="Username" aria-describedby="basic-addon1">
                            </div>

                            <div class="list-group chat_tab_users nav-pills list_tab_users" data-status="1" id="list-tab"
                                role="tablist">

                                @include('users.includes.chat.index_chat_rooms')

                            </div>
                        </div>


                        <div class="col-8">
                            <div class="tab-content box_msgs" id="nav-tabContent">
                                @include('users.includes.chat.index_chat_boxes')
                            </div>

                        </div>
                    </div>

                </div>
            </div>
    </div>
@endsection
