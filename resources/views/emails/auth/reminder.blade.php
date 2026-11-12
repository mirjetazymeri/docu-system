<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>{{ __('ui.password_reset') }}</h2>

		<div>
			{{ __('ui.password_reset_form') }} {{ URL::to('password/reset', $token) }}.<br/>
			{{ __('ui.password_reset_expiry', ['minutes' => Config::get('auth.reminder.expire', 60)]) }}
		</div>
	</body>
</html>
