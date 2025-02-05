<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author    Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license   MIT
 */

namespace IFRS\Transactions;

use IFRS\Interfaces\Assignable;
use IFRS\Interfaces\Fetchable;

use IFRS\Traits\Assigning;
use IFRS\Traits\Fetching;

use IFRS\Models\Account;
use IFRS\Models\LineItem;
use IFRS\Models\Transaction;

use IFRS\Exceptions\LineItemAccount;
use IFRS\Exceptions\MainAccount;
use IFRS\Exceptions\VatCharge;

class SupplierPayment extends Transaction implements Assignable
{
    use Assigning;

    use \Parental\HasParent;

    /**
     * Transaction Number prefix
     *
     * @var string
     */

    const PREFIX = Transaction::PY;

    /**
     * Construct new ContraEntry
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['credited'] = false;
        $attributes['transaction_type'] = self::PREFIX;

        parent::__construct($attributes);
    }

    /**
     * Validate SupplierPayment Main Account
     */
    public function save(array $options = []): bool
    {
        if (is_null($this->account) || $this->account->account_type != Account::PAYABLE) {
            throw new MainAccount(self::PREFIX, Account::PAYABLE);
        }

        return parent::save();
    }

    /**
     * Validate SupplierPayment LineItems
     */
    public function addLineItem(LineItem $lineItem): bool
    {
        if ($lineItem->account->account_type != Account::BANK) {
            throw new LineItemAccount(self::PREFIX, [Account::BANK]);
        }

        if ($lineItem->vat->rate > 0) {
            throw new VatCharge(self::PREFIX);
        }

        return parent::addLineItem($lineItem);
    }
}
