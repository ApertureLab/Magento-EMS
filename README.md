Description
-----------

Baobaz EMS sync Magento newsletter subscribers with Experian CheetahMail (= Emailing Solution = EMS).

Features list:

* Sync Magento newsletter subscribers (anonymous ou customers) with EMS
* Fields mapping between Magento customer attributes and EMS fields
* EMS fields list (+ description) autoloaded in backend
* Opt'in dedicated field

Configuration
-------------

* Config
 * System > Configuration > Customers > Newsletter > EMS Settings
  * Login: EMS Soap account login
  * Password: EMS Soap account password
  * List ID: EMS base ID
  * Use proxy: if enabled, add proxy IP and port
  * Field Mapping: mapping between Magento Customer attributes and EMS fields
* Config file (config.xml)
 * global > settings > ems > soap
  * wsdl
