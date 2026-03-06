pipeline {
    agent any

    environment {
        APP_NAME = "pos-textile"
    }

    options {
        disableConcurrentBuilds()
        buildDiscarder(logRotator(numToKeepStr: '5'))
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Setup ENV') {
            steps {
                // Sesuaikan path .env di server kamu
                sh 'cp /home/dev/pos-textile/.env .env'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh 'make build'
            }
        }

        stage('Deploy') {
            steps {
                sh 'make deploy'
            }
        }
    }

    post {
        success {
            echo '✅ Deploy berhasil!'
        }
        failure {
            echo '❌ Deploy gagal!'
        }
    }
}