# HttpResponce class

## Contao 4

In Contao 4 the X-Frame-Options settings are populated by the [NelmioSecurityBundle](https://github.com/nelmio/NelmioSecurityBundle) and the Haste Plus settings will be overwritten. In the contao manager bundle all sites are set to `X-Frame-Options: SAMEORIGIN` by the [bundle settings](https://github.com/contao/manager-bundle/blob/master/src/Resources/contao-manager/nelmio_security.yml). Because of the config handling from Symfony and NelmioSecurityBundle, this can't be overwritten by the settings (it's always the first and will always match). The only way we found is to implement a compiler pass and overwrite it there or move the corresponding value at the bottom of the array (shown in example)¹:

```
$paths = $container->getParameter('nelmio_security.clickjacking.paths');
$key = '^/.*';
$catchAll = $paths[$key];
unset($paths[$key]);
$paths[$key] = $catchAll;
$container->setParameter('nelmio_security.clickjacking.paths', $paths);
```
¹ *state 17.10.2017 with Contao 4.4.7 and Symfony 3.3.10. If the situations changes or you found a better solution, please don't hesitate to write an issue or pull request.*
