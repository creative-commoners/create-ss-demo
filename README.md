# create-ss-demo

[![Build Status](https://travis-ci.org/creative-commoners/create-ss-demo.svg?branch=master)](https://travis-ci.org/creative-commoners/create-ss-demo)

A command line utility that creates demo sites using the SilverStripe Platform API.

## Requirements

* PHP ^7.1
* Docker to create containers
* A Docker Hub account to store the container tags
* SilverStripe Platform stack permissions for the content snapshots
* [sspak](https://github.com/silverstripe/sspak) to create snapshots

## Installation

Install globally with Composer:

```
composer global require creative-commoners/create-ss-demo dev-master
```

Ensure the global Composer bin folder is in your system path.

## Configuration

Ensure you have logged into your Docker Hub account:

```
docker login
```

## Usage

You have a local environment ready to share with someone, and you want to publish it. The steps involved are:

* Build a Docker container
* Publish it
* Create the demo instance
