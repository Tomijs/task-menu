# Menu manager


## Table of Contents
- [Task description](#task-description)
- [Routes](#routes)
- [Bonus points](#bonus-points)


## Task Description

Fork or Download this repository and implement the logic to manage a menu.

A Menu has a depth of **N** and maximum number of items per layer **M**. Consider **N** and **M** to be dynamic for bonus points.

It should be possible to manage the menu by sending API requests. Do not implement a frontend for this task.

Feel free to add comments or considerations when submitting the response at the end of the `README`.


### Example menu

* Home
    * Home sub1
        * Home sub sub
            * [N] 
    * Home sub2
    * [M]
* About
* [M]


## Routes


### `POST /menus`

Create a menu.


#### Input

```json
{
    "field": "value"
}
```


##### Bonus

```json
{
    "field": "value",
    "max_depth": 5,
    "max_children": 5
}
```


#### Output

```json
{
    "field": "value"
}
```


##### Bonus

```json
{
    "field": "value",
    "max_depth": 5,
    "max_children": 5
}
```


### `GET /menus/{menu}`

Get the menu.


#### Output

```json
{
    "field": "value"
}
```


##### Bonus

```json
{
    "field": "value",
    "max_depth": 5,
    "max_children": 5
}
```


### `PUT|PATCH /menus/{menu}`

Update the menu.


#### Input

```json
{
    "field": "value"
}
```


##### Bonus

```json
{
    "field": "value",
    "max_depth": 5,
    "max_children": 5
}
```


#### Output

```json
{
    "field": "value"
}
```


##### Bonus

```json
{
    "field": "value",
    "max_depth": 5,
    "max_children": 5
}
```


### `DELETE /menus/{menu}`

Delete the menu.


### `POST /menus/{menu}/items`

Create menu items.


#### Input

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


##### Bonus

```json
[
    {
        "field": "value",
        "children": [
            {
                "field": "value",
                "children": []
            },
            {
                "field": "value"
            }
        ]
    },
    {
        "field": "value"
    }
]
```


#### Output

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


##### Bonus

```json
[
    {
        "field": "value",
        "children": [
            {
                "field": "value",
                "children": []
            },
            {
                "field": "value"
            }
        ]
    },
    {
        "field": "value"
    }
]
```


### `GET /menus/{menu}/items`

Get all menu items.


#### Output

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


##### Bonus

```json
[
    {
        "field": "value",
        "children": [
            {
                "field": "value",
                "children": []
            },
            {
                "field": "value"
            }
        ]
    },
    {
        "field": "value"
    }
]
```


### `DELETE /menus/{menu}/items`

Remove all menu items.


### `POST /items`

Create an item.


#### Input

```json
{
    "field": "value"
}
```


#### Output

```json
{
    "field": "value"
}
```


### `GET /items/{item}`

Get the item.


#### Output

```json
{
    "field": "value"
}
```


### `PUT|PATCH /items/{item}`

Update the item.


#### Input

```json
{
    "field": "value"
}
```


#### Output

```json
{
    "field": "value"
}
```


### `DELETE /items/{item}`

Delete the item.


### `POST /items/{item}/children`

Create item's children.


#### Input

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


##### Bonus

```json
[
    {
        "field": "value",
        "children": [
            {
                "field": "value",
                "children": []
            },
            {
                "field": "value"
            }
        ]
    },
    {
        "field": "value"
    }
]
```


#### Output

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


##### Bonus

```json
[
    {
        "field": "value",
        "children": [
            {
                "field": "value",
                "children": []
            },
            {
                "field": "value"
            }
        ]
    },
    {
        "field": "value"
    }
]
```


### `GET /items/{item}/children`

Get all item's children.


#### Output

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


##### Bonus

```json
[
    {
        "field": "value",
        "children": [
            {
                "field": "value",
                "children": []
            },
            {
                "field": "value"
            }
        ]
    },
    {
        "field": "value"
    }
]
```


### `DELETE /items/{item}/children`

Remove all children.


### `GET /menus/{menu}/layers/{layer}`

Get all menu items in a layer.


#### Output

```json
[
    {
        "field": "value"
    },
    {
        "field": "value"
    }
]
```


### `DELETE /menus/{menu}/layers/{layer}`

Remove a layer and relink `layer + 1` with `layer - 1`, to avoid dangling data.


### `GET /menus/{menu}/depth`


#### Output

Get depth of menu.

```json
{
    "depth": 5
}
```


## Bonus points

* 10 vs 1.000.000 menu items - what would you do differently?
* Write documentation
* Use PhpCS | PhpCsFixer | PhpStan
* Use cache
* Use data structures
* Use docker


## Comments / Considerations - by Toms

Task descriptions could be written in much more detail because quite many things are not clear, like

* What are development restrictions - anything is allowed or without 3rd party dependencies, etc.;
* Route requirements and output could be described in more detail, for example, at first I did not understand how POST /items route is different from POST /menus/{menu}/items;
* Layer is not clearly understandable without extra information;
* Also, difficult to understand if max_children is per layer or per parent item.


## Development results - by Toms

I managed to do most of the tasks written above.

* All routes (with "bonus" requirements) are developed but tested only by using the actual API calls and debugging;
* Created data factories and seeders;
* Used PhpCS and PhpCsFixer for PSR1 and PSR2 rules;
* Used Redis (PHPRedis) cache;
* Used data structures;
* Used docker.


## Possible development improvements - by Toms

API Authentication not included just because this is a prototype project.

Things that could be done better but were excluded because of time consumed for these tasks:

* Caching is added only to MenuController;
* Data gathering is not fully optimized. Should have used eager loading in few places but I have not tested if that would reduce SQL connections;
* Some validations could be optimized better with less SQL connections (have not tested - just thoughts);
* Tests should be written;
* Validations could be moved to separate files for cleaner and more structured code;
* Documention should be written.

Regarding the question "10 vs 1.000.000 menu items - what would you do differently?": few previously mentioned bullet-points related to optimization and caching would make the difference for higher load of storage.


## Docker and Laravel setup instructions - by Toms

[Docker Hub repository](https://hub.docker.com/r/tomys/cobiro_task-menu "Docker Hub repository")

Docker project structure *(docker files can be found in docker.zip archive inside this GitHub repository)*:
* app
	* Laravel project files (pull from this GitHub repository)
* nginx
	* conf.d
		* app.conf
* php
	* local.ini
* docker-compose.yml
* Dockerfile

Commands necessary for project to work:
* ```composer install```
* ```php artisan migrate:refresh --seed```
