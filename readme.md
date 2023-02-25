# AuthOpenIDConnect - LimeSurvey Auth Plugin

## Added
Compared to the main project by [Jan Menzel](https://github.com/janmenzel/AuthOpenIDConnect), I tested the plugin with Limesurvey v5 (5.6.7) and it works just fine. I also added a feature, that a user needs to have a specific role to login. This feature can be de-/activated and the role and claim Name can be adjusted. I need this feature myself, because I use keycloak and dont want everybody in my realm to access limesurvey, only the ones who got the keycloak group to access limesurvey. 

<img src="https://user-images.githubusercontent.com/34423885/221330345-f4716c68-183b-43d3-a452-8ae10094bfaf.png" width="700" />

So my userinfo Token looks like this:

<img src="https://user-images.githubusercontent.com/34423885/221330453-95675540-b0f5-462e-8a4d-d3b05ce78414.png" width="300" />

## Disclaimer
This plugin is **not maintained** by me anymore.\
Feel free to create a fork if you would like to customize it.

I am really grateful for your pull requests, but as I am not working with PHP and Limesurvey anymore I can't test them and ensure that everything works fine after a merge.

If you would like to continue to maintain this project please open an issue to get in touch with me. ☺

## Install

1. Download the plugin.

2. Install necessary dependencies via composer.
```
composer install
```

3. Zip the plugin with all dependencies installed.
```
zip -r AuthOpenIDConnect AuthOpenIDConnect/*
```

4. Install the plugin in LimeSurvey and fill in the necessary settings in order to connect to your ID Provider.

## Credits
Thanks to Michael Jett for providing the [OpenID Connect Client](https://github.com/jumbojett/OpenID-Connect-PHP)!

Thanks to Jan Menzel for providing the [AuthOpenIDConnect](https://github.com/janmenzel/AuthOpenIDConnect) plugin!
