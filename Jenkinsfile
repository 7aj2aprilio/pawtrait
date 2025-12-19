pipeline {
    agent any
    
    environment {
        DOCKER_IMAGE = 'pawtrait-app'
        DOCKER_TAG = "${BUILD_NUMBER}"
        DOCKER_REGISTRY = 'pioaprilio/pawtrait-app' // Ganti dengan registry Anda
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
                // Copy .env.example to .env if not exists
                sh '''
                    if [ ! -f .env ]; then
                        cp .env.example .env
                        echo "Created .env from .env.example"
                    fi
                '''
            }
        }
        
        stage('Code Quality') {
            steps {
                echo 'Running code quality checks...'
                // PHP Syntax Check
                sh '''
                    find . -name "*.php" -not -path "./vendor/*" | head -20 | xargs -I {} php -l {}
                '''
            }
        }
        
        stage('Build Docker Image') {
            steps {
                echo 'Building Docker image...'
                sh '''
                    docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} .
                    docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest
                '''
            }
        }
        
        stage('Test') {
            steps {
                echo 'Running tests...'
                sh '''
                    # Start containers for testing
                    docker-compose up -d db
                    
                    # Wait for database to be ready
                    sleep 30
                    
                    # Run application container for testing
                    docker run --rm --network pawtrait-network \
                        -e DB_HOST=db \
                        -e DB_USER=pawtrait \
                        -e DB_PASS=pawtrait_secret \
                        -e DB_NAME=photobooth_db \
                        ${DOCKER_IMAGE}:${DOCKER_TAG} \
                        php -r "echo 'PHP Configuration OK';"
                    
                    # Cleanup test containers
                    docker-compose down
                '''
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
                    sh '''
                        echo $DOCKER_PASS | docker login ${DOCKER_REGISTRY} -u $DOCKER_USER --password-stdin
                        docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}
                        docker tag ${DOCKER_IMAGE}:latest ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:latest
                        docker push ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:${DOCKER_TAG}
                        docker push ${DOCKER_REGISTRY}/${DOCKER_IMAGE}:latest
                    '''
                }
            }
        }
        
        stage('Deploy to Staging') {
            when {
                branch 'develop'
            }
            steps {
                echo 'Deploying to Staging...'
                sh '''
                    docker-compose down || true
                    docker-compose up -d
                '''
            }
        }
        
        stage('Deploy to Production') {
            when {
                branch 'main'
            }
            steps {
                echo 'Deploying to Production...'
                input message: 'Deploy to production?', ok: 'Deploy'
                sh '''
                    docker-compose -f docker-compose.yml down || true
                    docker-compose -f docker-compose.yml up -d
                '''
            }
        }
    }
    
    post {
        always {
            echo 'Cleaning up...'
            sh 'docker system prune -f || true'
        }
        success {
            echo 'Pipeline completed successfully!'
        }
        failure {
            echo 'Pipeline failed!'
        }
    }
}
