<?php

namespace Context\Page\Asset;

use Context\Page\Base\Form;

/**
 * Product asset edit page
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /** @var string */
    protected $path = '/enrich/asset/{id}/edit';

    /**
     * Fill a new date in the End of use at date picker
     *
     * @param string $date YEAR-MONTH-DAY e.g. 2015-06-20
     */
    public function changeTheEndOfUseAtTo($date)
    {
        $field = $this->find('css', 'label:contains("End of use at")');
        $this->fillDateField($field, $date);
    }
}
