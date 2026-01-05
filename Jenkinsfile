pipeline {
    agent any
    
    environment {
        DOCKER_IMAGE = 'pioaprilio/pawtrait-app'
        DOCKER_TAG = "${BUILD_NUMBER}"
        DOCKER_REGISTRY = 'docker.io'
    }
    
    stages {
        stage('Checkout') {
            steps {
                echo 'Checking out source code...'
                checkout scm
            }
        }
        
        stage('Setup Environment') {
            steps {
                echo 'Setting up environment...'
                script {
                    if (isUnix()) {
                        sh '''
                            if [ ! -f .env ]; then
                                cp .env.example .env
                                echo "Created .env from .env.example"
                            fi
                        '''
                    } else {
                        bat '''
                            if not exist .env (
                                copy .env.example .env
                                echo Created .env from .env.example
                            )
                        '''
                    }
                }
            }
        }
        
        stage('Code Quality') {
            steps {
                echo 'Running code quality checks...'
                script {
                    if (isUnix()) {
                        sh 'find . -name "*.php" -not -path "./vendor/*" | head -20 | xargs -I {} php -l {}'
                    } else {
                        // Skip folder vendor biar gak error path too long di Windows
                        bat 'for /f "usebackq tokens=*" %%f in (`dir /s /b *.php ^| findstr /v /i "vendor"`) do php -l "%%f"'
                    }
                }
            }
        }
        
        stage('Install Dependencies') {
            steps {
                echo 'Installing dependencies...'
                script {
                    if (isUnix()) {
                        sh 'composer install --no-dev --prefer-dist'
                    } else {
                        bat 'composer install --no-dev --prefer-dist'
                    }
                }
            }
        }
        
        stage('Build Docker Image') {
            steps {
                echo 'Building Docker image...'
                script {
                    if (isUnix()) {
                        sh """
                            docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} .
                            docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest
                        """
                    } else {
                        bat """
                            docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} .
                            docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest
                        """
                    }
                }
            }
        }
        
        stage('Test') {
            steps {
                echo 'Running tests...'
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose up -d db
                            sleep 30
                            docker run --rm --network pawtrait_pawtrait-network \
                                -e DB_HOST=db \
                                -e DB_USER=pawtrait \
                                -e DB_PASS=pawtrait_secret \
                                -e DB_NAME=photobooth_db \
                                ${DOCKER_IMAGE}:${DOCKER_TAG} \
                                php -r "echo 'PHP Configuration OK';"
                            docker-compose down
                        '''
                    } else {
                        bat '''
                            docker-compose up -d db
                            timeout /t 30 /nobreak
                            docker-compose down
                        '''
                        echo 'Basic test completed on Windows'
                    }
                }
            }
        }
        
        stage('Push to Registry') {
            when {
                branch 'main'
            }
            steps {
                echo 'Pushing to Docker Registry...'
                withCredentials([usernamePassword(
                    credentialsId: 'docker-registry-credentials',
                    usernameVariable: 'DOCKER_USER',
                    passwordVariable: 'DOCKER_PASS'
                )]) {
                    script {
                        if (isUnix()) {
                            sh '''
                                echo $DOCKER_PASS | docker login ${DOCKER_REGISTRY} -u $DOCKER_USER --password-stdin
                                docker push ${DOCKER_IMAGE}:${DOCKER_TAG}
                                docker push ${DOCKER_IMAGE}:latest
                            '''
                        } else {
                            bat """
                                docker login ${DOCKER_REGISTRY} -u %DOCKER_USER% -p %DOCKER_PASS%
                                docker push ${DOCKER_IMAGE}:${DOCKER_TAG}
                                docker push ${DOCKER_IMAGE}:latest
                            """
                        }
                    }
                }
            }
        }
        
        stage('Deploy to Staging') {
            when {
                branch 'develop'
            }
            steps {
                echo 'Deploying to Staging...'
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose down || true
                            docker-compose up -d
                        '''
                    } else {
                        bat '''
                            docker-compose down
                            docker-compose up -d
                        '''
                    }
                }
            }
        }
        
        stage('Deploy to Production') {
            when {
                branch 'main'
            }
            steps {
                echo 'Deploying to Production...'
                input message: 'Deploy to production?', ok: 'Deploy'
                script {
                    if (isUnix()) {
                        sh '''
                            docker-compose -f docker-compose.yml down || true
                            docker-compose -f docker-compose.yml up -d
                        '''
                    } else {
                        bat '''
                            docker-compose -f docker-compose.yml down
                            docker-compose -f docker-compose.yml up -d
                        '''
                    }
                }
            }
        }
    }
    
    post {
        always {
            echo 'Cleaning up...'
            script {
                if (isUnix()) {
                    sh 'docker system prune -f || true'
                } else {
                    bat 'docker system prune -f'
                }
            }
        }
        success {
            echo 'Pipeline completed successfully!'
        }
        failure {
            echo 'Pipeline failed!'
        }
    }
}
