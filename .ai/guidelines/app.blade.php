# App Guidelines

## Purpose

This application is used for the central management of application profiles and applications (including job advertisements). It also provides access to the data via API (Laravel Sanctum).

## Profiles (App\Models\Profile)

Multiple application profiles can be managed per user. Profiles are personal and can be assigned to any application. This makes it possible to use different profiles for different applications with different requirements.

## Applications (App\Models\Application)

Applications contain all the information necessary for applying to a company.

## Goals

* Be able to track where users have already applied to avoid duplicate applications and see the status of an application.
* Short term: Automatic deployment via GitHub Actions to the shared host netcup GmbH (netcup.com) with Plesk as the hosting management system.
* Long term: Generate applications as PDF files and allow users to choose whether to send online applications, PDF files, or both when submitting.
