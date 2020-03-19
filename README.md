# Twig for Contao Templates

### How to use
1. Make sure you have your `twig.default_path` set. For example like this:
    ```yaml
    twig:
      default_path: '%kernel.project_dir%/templates' 
    ``` 

2. Put a template you want to provide as a twig version inside your template
   directory (can be any subdirectory). Name it like the Contao template
   but with `.html.twig` as file extension instead of `.html5`.
   
   For example put a `ce_downloads.html.twig` file under `templates/Contao`.
 
3. Rebuild your cache (the filesystem is scanned for templates in a compiler pass).
   Your new template is now rendered - it has the same context set as the
   existing Contao one has.

#### Caveats
As Contao uses input encoding, you'll need to deal for already encoded variables
yourself by adding the `|raw` filter. Use with caution and be sure you know what
you are doing.

Some contao templates contain closures that won't be evaluated by Twig - if you
want to use them wrap them in the `fn()` function shipping with this bundle. 
This will simply execute them and return the 'safe' output (no need for `|raw`). 
   
#### Example
```twig
{# templates/Contao/ce_downloads.html.twig #}

<div class="ce_downloads --this-looks-nice">    
    <ul>
        {% for file in files %}
            <li class="ext-{{ file.extension }}">
                <a href="{{ file.href|raw }}" title="{{ file.title }}" type="{{ file.mime }}">
                    {{ file.link }} <span>({{ file.filesize }})</span>
                </a>
            </li>
        {% endfor %}
    </ul>
</div>
```