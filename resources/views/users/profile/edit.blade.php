@extends('layouts.app')

@section('content')
    @if (Session::has('success'))
        <div class="alert alert-success text-center">{{ Session::get('success') }}</div>
    @endif

    @if (Session::has('error'))
        <div class="alert alert-danger text-center">{{ Session::get('error') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update','auth') }}" enctype="multipart/form-data">
        @csrf
        @method('put')

        <div class="card text-white bg-dark mb-3" style="max-width: 34rem;margin-top: 20px">
            <div class="card-header">update profile</div>

            <div class="card-body">

                <div class="form-group">
                    <label for="exampleInputEmail1">
                        location
                    </label>
                    <select class="form-select" required name="location" aria-label="Default select example">
                        <option value="">...</option>
                        {{$location = $user_info ? $user_info->location : ''}}
                        @foreach ($countries as $country)

                            <option @selected($country == $location) value="{{ $country }}">
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>

                    @error('location')
                        <small style="color: red">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="exampleInputEmail1">
                        you will be
                    </label>
                    <select class="form-select" required name="type"  aria-label="Default select example">
                        {{$type = $user_info ? $user_info->type : ''}}

                            <option @selected('freelancer' == $type) value="freelancer">freelancer</option>
                            <option @selected('client' == $type) value="client">client</option>
                        
                    </select>

                    @error('type')
                        <small style="color: red">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">
                        card number
                    </label>

                    <input required maxlength="16" value="{{$user_info->card_num}}" minlength="12" type="text" name="card_num" class="form-control" ></input>
                    @error('card_num')
                        <small style="color: red">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">
                        job
                    </label>
                    <input type="text" required max="30" min="3" value="{{$user_info ? $user_info->job :'' }}" name="job" class="form-control">
                    @error('job')
                        <small style="color: red">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="exampleInputPassword1">
                        overview
                    </label>
                    <textarea type="text" required max="250" min="3"  name="overview" class="form-control"  cols="30" rows="4">
                        {{$user_info ? $user_info->overview : ''}}
                    </textarea>
                    @error('overview')
                        <small style="color: red">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                <div class="form-group" style="margin-top: 20px;width: 50%">
                    <label for="exampleInputEmail1">
                        photo
                    </label>

                    <input type="file"  name="image" class="form-control" aria-describedby="emailHelp">
                    @error('image')
                        <small style="color: red">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-primary"
            style="margin-top: 10px;margin-bottom: 10px">
            update
        </button>

    </form>
@endsection
