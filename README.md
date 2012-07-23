Description
-----------

_Baobaz EMS_ synchronizes Magento with Experian CheetahMail.

Note: EMS = Emailing Solution.

Features list:

* Synchronize Magento Newsletter subscribers (anonymous or customers) with CheetahMail database through the Soap WebService
* Fields mapping between Magento customers' attributes and CheetahMail fields
* CheetahMail fields list (+ description) autoloaded in backend
* Opt'in dedicated field


Configuration
-------------

* Config
 * System > Configuration > Customers > Newsletter > EMS Settings
  * Login: EMS Soap account login
  * Password: EMS Soap account password
  * List ID: EMS database ID
  * Use proxy: if enabled, add proxy IP and port
  * Field Mapping: mapping between Magento Customers' attributes and CheetahMail fields
* Config file (config.xml)
 * global > settings > ems > soap
  * wsdl


Screenshot
----------

![Baobaz_Ems Configuration](https://github.com/Baobaz/Magento_Baobaz_Ems/raw/master/doc/screenshots/Baobaz_Ems-Configuration_4.png "Baobaz_Ems Configuration")
