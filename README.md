Magento 1 module that synchronizes Magento Newsletter Subscribers with Experian Marketing Services (EMS) CheetahMail database.

_[Press release](https://diigo.com/0fb7jk)_

## Features

* Synchronizes Magento Newsletter Subscribers (guests and customers) with CheetahMail database through the Soap WebService
* Configurable mapping between Magento customers' attributes and CheetahMail fields
* CheetahMail fields list (+ description) autoloaded in backend
* Opt'in dedicated field
* HTTP proxy compliant

## Screenshot

![Baobaz_Ems Configuration](/doc/screenshots/Baobaz_Ems-Configuration_5.png "Baobaz_Ems Configuration")

## Configuration

### Config
* System > Configuration > Customers > Newsletter > EMS Settings
   * Login: EMS Soap account login
   * Password: EMS Soap account password
   * List ID: EMS database ID
   * Test connection: Test your connection with WS before saving
   * Field Mapping: mapping between Magento Customers' attributes and CheetahMail fields
   * Use proxy: if enabled, add proxy IP and port

### Config file (config.xml)
* global > settings > ems
   * soap
      * wsdl: change wsdl URL pattern if necessary
   * debug: write debug log in log/debug.ems.log
   * debug_soapclient: write debug log of Soap Client connection only
* global > crontab > jobs
   * baobaz_ems_scheduled_actions
      * schedule > cron_expr: updates schedule if necessary

## How to?

### Add custom attributes

1rst step: Rewrite Customer adpater model

app\code\local\`{Namespace}`\Ems\etc\config.xml:
```xml
<global>
    <models>
        <baobaz_ems>
            <rewrite>
                <adapter_customer>{Namespace}_Ems_Model_Adapter_Customer</adapter_customer>
            </rewrite>
        </baobaz_ems>
    </models>
</global>
```

2nd step: Define your custom attributes

app\code\local\`{Namespace}`\Ems\Model\Adapter\Customer.php:
```php
<?php
class {Namespace}_Ems_Model_Adapter_Customer extends Baobaz_Ems_Model_Adapter_Customer
{
    /**
     * Get 2 new custom attributes
     */
    public function _getAttributes()
    {
        $attributes = parent::_getAttributes();
        
        $attributes['attribute_1_code'] = 'attribute_1_name';
        $attributes['attribute_2_code'] = 'attribute_2_name';

        return $attributes;
    }
}
```

### Add fields mappers

1rst step: Rewrite Customer adpater model

app\code\local\`{Namespace}`\Ems\etc\config.xml:
```xml
<global>
    <models>
        <baobaz_ems>
            <rewrite>
                <adapter_customer>{Namespace}_Ems_Model_Adapter_Customer</adapter_customer>
            </rewrite>
        </baobaz_ems>
    </models>
</global>
```

2nd step: Override Magento Customer method

app\code\local\`{Namespace}`\Ems\Model\Adapter\Customer.php:
```php
<?php
class {Namespace}_Ems_Model_Adapter_Customer extends Baobaz_Ems_Model_Adapter_Customer
{
    /**
     * Mapper for Civility / Prefix field
     */
    public function getPrefix()
    {
        $prefix = $this->getCustomer()->getPrefix();
        switch ($prefix) {
            case 'MR':
                $value = 'Monsieur';
                break;
            case 'MME':
                $value = 'Madame';
                break;
            case 'MLE':
                $value = 'Mademoiselle';
                break;
            default:
                $value = '';
        }
        return $value;
    }
}
```

## License

Released under the terms of the [Open Software License 3.0](http://opensource.org/licenses/OSL-3.0).

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
