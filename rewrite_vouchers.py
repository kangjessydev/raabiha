import os

def replace_in_file(filepath, replacements):
    with open(filepath, 'r') as f:
        content = f.read()
    
    for old, new in replacements:
        content = content.replace(old, new)
        
    with open(filepath, 'w') as f:
        f.write(content)

replacements_php = [
    ('public $couponCode', 'public $voucherCode'),
    ('public $appliedCoupon', 'public $appliedVoucher'),
    ('applied_coupon', 'applied_voucher'),
    ('coupon_error', 'voucher_error'),
    ('applyCoupon', 'applyVoucher'),
    ('selectCoupon', 'selectVoucher'),
    ('removeCoupon', 'removeVoucher'),
    ('App\\Models\\Coupon', 'App\\Models\\Voucher'),
    ('valid_until', 'expires_at'),
    ('valid_from', 'starts_at'),
    ('usage_limit', 'max_uses'),
    ('min_spend', 'min_purchase'),
    ('discount_value', 'discount_amount'),
    ('availableCoupons', 'availableVouchers'),
    ('$appliedCoupon', '$appliedVoucher'),
    ('$coupon', '$voucher'),
    ('$this->couponCode', '$this->voucherCode')
]

replace_in_file('app/Livewire/Cart.php', replacements_php)
replace_in_file('app/Livewire/Checkout.php', replacements_php)

replacements_blade = [
    ('bsVoucherOpen', 'bsVoucherOpen'), # Just to check
    ('coupon_error', 'voucher_error'),
    ('couponCode', 'voucherCode'),
    ('applyCoupon', 'applyVoucher'),
    ('selectCoupon', 'selectVoucher'),
    ('removeCoupon', 'removeVoucher'),
    ('availableCoupons', 'availableVouchers'),
    ('$appliedCoupon', '$appliedVoucher'),
    ('$coupon', '$voucher'),
    ('discount_value', 'discount_amount'),
    ('min_spend', 'min_purchase'),
    ('percentage', 'percent')
]

replace_in_file('resources/views/livewire/cart.blade.php', replacements_blade)
replace_in_file('resources/views/livewire/checkout.blade.php', replacements_blade)

print("Replacement complete.")
