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
   
   **Note**: for a better DX your templates will always
             be loaded in the *dev* environment.  
   
   That's it. Your new template is now rendered instead. It has the same context
   as the existing Contao one would have (`Template->getData()`). :sparkles:

#### Render event
The system dispatches an event directly *before* a twig template gets rendered. It 
allows altering the template context or even the actual template that is going to be 
rendered:

```yml
# config/services.yaml
services:
    App\EventListener\RenderTemplateListener:
        tags:
            - { name: kernel.event_listener, event: 'Mvo\ContaoTwig\Event\RenderTemplateEvent' }
```

```php
// App\EventListener\RenderTemplateListener

public function __invoke(\Mvo\ContaoTwig\Event\RenderTemplateEvent  $event): void {
    $context = $event->getContext(); // the template's context
    $template = $event->getTemplate(); // the template's name
    $contaoTemplate = $event->getContaoTemplate(); // the original Contao template
    
    // …
    
    $event->setTemplate('another-template.html.twig');
    $event->setContext(array_merge($context, ['foo' => 'bar']));
}
```

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