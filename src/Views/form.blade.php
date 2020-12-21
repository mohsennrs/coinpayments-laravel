@if(config('coinpayments.cp_merchant'))
    <form id='crypto-form' method='post' class='form' action='https://www.coinpayments.net/index.php' hidden='hidden'>
        <input type='hidden' name='cmd' value='_pay_simple'>
        <input type='hidden' name='reset' value='1'>
        <input type='hidden' name='merchant' value='{{config('coinpayments.cp_merchant')}}'>
        <input type='hidden' name='currency' value='{{ $args->currency }}'>
        <input type='hidden' name='amountf' value='{{ $args->amountf }}'>
        <input type='hidden' name='allow_currency' value='0'>
        <input type='hidden' name='allow_currencies' value='{{ $args->allow_currencies }}'>
        @if (isset($args->tag))
            <input type='hidden' name='dest_tag' value='{{$args->tag}}'>
        @endif
        <input type='hidden' name='item_name' value='Buy Cryptocurrency'>
        <input type='hidden' name='lang' value='en'>
        <input type='hidden' name='success_url' value='{{config('coinpayments.cp_success')."?invoice=".$args->payment_id }}'>
        <input type='hidden' name='cancel_url' value='{{config('coinpayments.cp_error')."?invoice=".$args->payment_id}}'>
        <input type='hidden' name='ipn_url' value='{{ config('coinpayments.cp_ipn')."?invoice=".$args->payment_id }}'>
        @if(isset($args->first_name))
            <input type='hidden' name='first_name' value='{{ $args->first_name }}'>
        @endif
        @if(isset($args->last_name))
            <input type='hidden' name='last_name' value='{{ $args->last_name }}'>
        @endif
        @if(isset($args->payment_id))
            <input type='hidden' name='invoice' value='{{ $args->payment_id }}'>
        @endif
        <input type='hidden' name='email' value='{{ $args->email }}'>
    </form>
@else

    <script>
        alert('INVALID COINPAYMENT MERCHANT CODE!');
    </script>
@endif