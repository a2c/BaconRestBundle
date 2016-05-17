<?php

namespace {{ namespace }}\Controller\Rest{{ entity_namespace ? '\\' ~ entity_namespace : '' }};
{% block use_statements %}
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

{% if 'annotation' == format -%}
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

{%- endif %}

use Bacon\Bundle\RestBundle\Controller\BaseController;
use {{ namespace }}\Entity\{{ entity }};
use {{ namespace }}\Form\Type\{{ entity }}FormType;
use {{ namespace }}\Form\Handler\{{ entity }}FormHandler;

{% if 'get' in actions %}
use FOS\RestBundle\Controller\Annotations\Get;
{% endif %}
{% if 'post' in actions %}
use FOS\RestBundle\Controller\Annotations\Post;
{% endif %}
{% if 'put' in actions %}
use FOS\RestBundle\Controller\Annotations\Put;
{% endif %}
{% if 'delete' in actions %}
use FOS\RestBundle\Controller\Annotations\Delete;
{% endif %}
{% endblock use_statements %}
{% block class_definition %}
Class {{ entity_class }}Controller extends BaseController
{% endblock class_definition %}
{
{% block class_body %}
    {%- if 'get' in actions %}
        {%- include 'crud/actions/rest/get.php.twig' %}
        {%- include 'crud/actions/rest/get_one.php.twig' %}
    {%- endif %}

    {%- if 'put' in actions %}
        {%- include 'crud/actions/rest/put.php.twig' %}
    {%- endif %}

    {%- if 'post' in actions %}
        {%- include 'crud/actions/rest/post.php.twig' %}
    {%- endif %}
    
    {%- if 'delete' in actions %}
        {%- include 'crud/actions/rest/delete.php.twig' %}
    {%- endif %}
   
{% endblock class_body %}

}