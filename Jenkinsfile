node ('virtual-web-slave') {

    prepareEnv()

    stage ('Git Checkout'){
        checkout scm
    }
/*
    stage ('unit test'){
        configFileProvider([configFile(fileId: "$configFileId", targetLocation: "config.php")]) {}
        //def cmd = "$php /usr/bin/composer update --no-scripts && $php vendor/bin/doctrine orm:schema-tool:update --force"
        def cmd = "$php /usr/bin/composer update --no-scripts"
        sh "$cmd"
        def unitCmd = "export SYMFONY_DEPRECATIONS_HELPER=disabled codecept run && $php vendor/bin/codecept run --xml --coverage --coverage-xml"
        sh "$unitCmd"
        step([
            $class: 'CloverPublisher',
            cloverReportDir: 'reports',
            cloverReportFileName: 'coverage.xml',
        ])
    }*/
/*
     stage ('sonarqube'){
         def scannerHome = tool 'SonarScanner'; //Sonar scanner binaries automatically installed from jenkins tools configuration
         def sonarCommand = "${scannerHome}/bin/sonar-scanner  -X \
         -Dsonar.projectName=$sonarProjectName \
         -Dsonar.projectKey=$sonarProjectKey"

         withSonarQubeEnv('SonarQube') { //relies on SonarQube Server installation in jenkins and Sonar-project.properties file at root level
             sh "$sonarCommand"
         }
     }
*/
    if(release){
        /* deployment */
        stage ('build') {
            def rsyncCmd = "rsync -e ssh \
                --exclude cache --exclude log --exclude .git --exclude vendor --exclude config.php \
                -rvau . icopy@172.20.1.171:$buildHome"

            echo "##### copying files using rsync #####"
            sh "$rsyncCmd"

            def postBuildCmd = "cd $buildHome && composer update && php vendor/bin/doctrine orm:schema-tool:update --force \
                            && ./postbuild.sh"
            echo "##### running composer install #####"
            sshPublisher(publishers: [
                sshPublisherDesc(
                    configName: "ic",
                    transfers: [sshTransfer(
                        execCommand: "$postBuildCmd",
                        execTimeout: 120000
                    )],
                    verbose: true
                )
            ])
        }
        stage('notify'){
            slackSend channel: slackChannel,
            color: '#00ff00',
            iconEmoji: 'tada',
            message: " Project has been successfully deployed on $environment environment :tada::tada:",
            tokenCredentialId: '8f20b805-6c73-4808-bf28-04c9e46c22c4'
        }
    }
}

void prepareEnv(){
    /* php version */
    php = "/usr/bin/php7.4"
    release = false
    sonarProjectName = "IC_WEB_FEATURE"
    sonarProjectKey = "IC:IC_WEB_FEATURE"
    configFileId =  "75ec8187-9671-4f4c-85b5-28476c7cccb7"
	if (env.BRANCH_NAME ==~ /^develop.*/) {
		sonarProjectName = "IC_WEB_DEVELOP"
		sonarProjectKey = "IC:IC_WEB_DEVELOP"
        buildHome = "/var/www/inspectioncopy.dev.mobelite.fr"
        environment = "develop"
        slackChannel = "ic_dev"
        release = true
	}

	if (env.BRANCH_NAME ==~ /^staging.*/) {
        buildHome = "/var/www/inspectioncopy.staging.mobelite.fr"
        environment = "staging"
        sonarProjectName = "IC_WEB_STAGING"
        sonarProjectKey = "IC_WEB_STAGING"
        slackChannel = "inspectioncopy"
        release = true
	}

	if (env.BRANCH_NAME ==~ /^release.*/) {
		sonarProjectName = "IC_WEB_RELEASE"
		sonarProjectKey = "IC:IC_WEB_RELEASE"
        buildHome = "/var/www/inspectioncopy.preprod.mobelite.fr"
        environment = "preprod"
        slackChannel = "inspectioncopy"
        release = true
	}
}
