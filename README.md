# CloudTool

A command line utility to configure containers easily using
templates.

```
cloudtool --autoload /opt/bootstrap.php 
```


## TL;DR


## Writing Config files

Just place a config file in the correct folder and append the extension `.php`.

Within the file you can generate output as you want:

```php
\Phore\CloudTool\CloudToolTemplate::Get()->setOnChangeAction()
```
