@extends('errors.layout')

@section('title', 'Internal Server Error')
@section('code', '500')
@section('message', isset($exception) && $exception ? $exception->getMessage() . ' IN ' . $exception->getFile() . ':' . $exception->getLine() : 'Whoops! Something went wrong on our servers.')
