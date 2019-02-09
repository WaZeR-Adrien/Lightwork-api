# Lightwork API [v4.0.0]
Lightwork API is a light **PHP framework** object oriented with many features in MVC.

## Installation :
- Clone the repository
- Rename kernel/Config-sample.php by kernel/Config.php
- Edit config by your own information
- Execute : composer install
- Optional (to use token system) : create a table Auth with 4 fields (pk int id, fk int user_id, string token, string date)
to use the secure system route by token

## More info :
- Many examples are available in files of the framework like the routes (in index.php and documentation)
- The documentation of your API is auto generate
- For the documentation generation to be perfect, fill in the information on each of the routes in the index.php

## Structure of files :

- `App` : Your MVC Files : Controllers / Models / Views (for doc)
- `Kernel` : The kernel of the website : Config / Router / Http / Database / Logs...
- `Public` : Your public files : JS / CSS...
- `Main index.php` : The main file of the site with the autoload, routing...

## Summary :
- [The routing](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Routing)
- [Create Controller](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Controllers)
- [The Request](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Request)
- [The Response](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Response)
- [Database & Models](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Database-&-Models)
- [Many tools](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Tools)
- [Logs](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Logs)
- [Your documentation](https://github.com/WaZeR-Adrien/Lightwork-api/wiki/Doc)

## Contact :
You can contact me :
- With my personal website -> https://adrien-martineau.fr/me-contacter/
- By open an issue in this repository
