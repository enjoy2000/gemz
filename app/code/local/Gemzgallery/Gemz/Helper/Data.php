<?php
class Gemzgallery_Gemz_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getListStyles()
    {

        $listStyle = [
            'Bracelets' => [
                'style' => [
                    'Bangle',
                    'Chain',
                    'Charm',
                    'Cuff',
                    'Strand',
                    'Tennis',
                    'Wrap'
                ],
                /**
                'metal' => [
                'Gold',
                'Platinum',
                'Rose Gold',
                'Silver',
                'Sterling',
                'White Gold',
                'Yellow Gold'
                ],
                'stone' => [
                'Amethyst',
                'Aquamarine',
                'Citrine',
                'Crystal',
                'Diamond',
                'Emerald',
                'Garnet',
                'Onyx',
                'Opal',
                'Pear',
                'Peridot',
                'Quartz',
                'Ruby',
                'Sapphire',
                'Tanzanite',
                'Topaz',
                'Tourmaline',
                'Turquoise',
                ],
                 * */
            ],
            'Cufflinks' => [
                'style' => [
                    'Around Cuff',
                    'Through Cuff'
                ]
            ],
            'Earrings' => [
                'style' => [
                    'Chandelier',
                    'Clip',
                    'Drop',
                    'Hoop',
                    'Stud',
                ],
            ],
            'Necklaces' => [
                'style' => [
                    'Chain',
                    'Collar',
                    'Pendant',
                    'Statement',
                    'Strand'
                ],
            ],
            'Rings' => [
                'style' => [
                    'Band',
                    'Engagement',
                    'Statement',
                ],
            ]
        ];

        return $listStyle;
    }

    /**
    * Return collection of designers need to show on homepage menu
    */
    public function getDesignersForHomeMenu()
    {
        $cache = Mage::getSingleton('core/cache');

        if (!$cache->load('home_designers')) {
            $sellerNames = [
                'dorianandrose@gmail.com',
                'andrea@kokku.co.uk',
                'elliot@dilamani.com',
                'bernd.wilhelm@berndwilhelm.com',
                'Jane@JaneGordon.com',
                'irit@iritdesign.com',
                'mknopp0816@aol.com',
                'info@vladimirmarkin.com',
                'shawnyousef@yahoo.com',
                'brenda@brendasmithjewelry.com',
                'martin@martinbernstein.com',
                'shekhar@realgemsinc.com',
                'atousa@michaeljohnjewelry.com',
                'info@janetaylor.com',
                'kerri@madstonedesign.com',
                'sales@designsbyhc.com',
                'usastuds1@gmail.com',
                'amanda@goshwara.com',
                'info@jbdiamonds.com',
                'aline.kfoury@nadag.com',
                'jude@judefrances.com',
    	        'kaurajewels@gmail.com',
                'info@goldneilson.com',
                'propositionlove@gmail.com',
                'frey@freyana.com',
                'deirdre@denise-james.com',
                'S.Rezac@sbcglobal.net',
                'info@erikstewartjewelry.com',
                'orlyravitz@012.net.il',
                'info@kaalidesigns.com',
                'Giulia@dinnyhall.co.uk',
            ];
            $sellers = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToSelect(['company_name', 'email', 'firstname', 'lastname'])
                //->addExpressionAttributeToSelect('full_name','CONCAT({{firstname}},"  ",{{lastname}})', array('firstname','lastname')) //** Step 1: create the new attribute/column – combination of first name<space>last name. I called it 'full_name'
                //->addAttributeToFilter('full_name',array('in' => $sellerNames)) //** Step 2: use the new attribute/column as a filter.
                ->addAttributeToFilter('email', ['in' => $sellerNames])
                ->addAttributeToSort('company_name', 'asc')
                ->getItems()
            ;

            $cache->save(serialize($sellers), 'home_designers', ['home_page'], 60*60*24);
        }

        return unserialize($cache->load('home_designers'));
    }
}
	 
