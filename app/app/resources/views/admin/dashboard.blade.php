@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Admin Dashboard</h1>
    <ul>
        <li>Total users: {{ $userCount }}</li>
        <li>Active subscriptions: {{ $activeSubscriptions }}</li>
        <li>Total revenue (in cents): {{ $revenue }}</li>
    </ul>
</div>
@endsection
