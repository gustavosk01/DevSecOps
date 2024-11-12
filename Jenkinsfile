pipeline {
    agent any
    
    environment {
        SNYK_TOKEN = credentials('SNYK')
        DOCKER_HUB = credentials('DOCKER_HUB')
        MYSQL_ROOT_PASSWORD = credentials('MYSQL_ROOT_PASSWORD')
        MYSQL_DATABASE = credentials('MYSQL_DATABASE')
        MYSQL_USER = credentials('MYSQL_USER')
        MYSQL_PASSWORD = credentials('MYSQL_PASSWORD')
        DB_HOST = credentials('DB_HOST')
        DB_USER = credentials('DB_USER')
        DB_PASSWORD = credentials('DB_PASSWORD')
        DB_TABLE = credentials('DB_TABLE')
    }
    
    stages {
        stage('Checkout') {
            steps {
                sh 'git clone https://github.com/gustavosk01/DevSecOps.git'
            }
        }

        stage('SCA') {
            steps {
                sh 'snyk auth --auth-type=token $SNYK_TOKEN'
                dir('DevSecOps/dmz/htdocs/back-end') {
                    sh 'snyk test'
                }
            }
        }

        stage('SAST') {
            steps {
                dir('DevSecOps/dmz/htdocs') {
                    sh 'snyk auth --auth-type=token $SNYK_TOKEN'
                    sh 'snyk code test -d'
                }
            }
        }

        stage('Docker build - db') {
            steps {
                dir('DevSecOps/db') {
                    sh 'docker build -t $DOCKER_HUB_USR/db:latest .'
                }
            }
        }

        stage('Docker build - dmz') {
            steps {
                dir('DevSecOps/dmz') {
                    sh 'docker build -t $DOCKER_HUB_USR/dmz:latest .'
                }
            }
        }
        
        stage('Push docker images') {
            steps {
                sh 'echo $DOCKER_HUB_PSW | docker login --username $DOCKER_HUB_USR --password-stdin'
                sh 'docker push $DOCKER_HUB_USR/db:latest'
                sh 'docker push $DOCKER_HUB_USR/dmz:latest'
            }
        }

        stage('Deploy DB') {
            steps {
                withCredentials([file(credentialsId: 'KUBECONFIG', variable: 'KUBECONFIG')]) {
                    script {
                        sh '''
                        microk8s kubectl delete secret db-secrets --ignore-not-found
                        microk8s kubectl create secret generic db-secrets \
                            --from-literal=MYSQL_ROOT_PASSWORD="$MYSQL_ROOT_PASSWORD" \
                            --from-literal=MYSQL_DATABASE="$MYSQL_DATABASE" \
                            --from-literal=MYSQL_USER="$MYSQL_USER" \
                            --from-literal=MYSQL_PASSWORD="$MYSQL_PASSWORD"
                        
                        microk8s kubectl delete -n default persistentvolumeclaim --all --ignore-not-found
                        microk8s kubectl delete -n default persistentvolume db-pv --ignore-not-found
                        

                        microk8s kubectl delete -f DevSecOps/kubernetes/db_stateful_set.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/db_service.yaml --ignore-not-found

                        microk8s kubectl apply -f DevSecOps/kubernetes/db_pv.yaml
                        microk8s kubectl apply -f DevSecOps/kubernetes/db_service.yaml
                        microk8s kubectl apply -f DevSecOps/kubernetes/db_stateful_set.yaml
                        '''
                    }
                }
            }
        }

        stage('Deploy DMZ') {
            steps {
                withCredentials([file(credentialsId: 'KUBECONFIG', variable: 'KUBECONFIG')]) {
                    script {
                        sh '''
                        microk8s kubectl delete secret dmz-secrets --ignore-not-found
                        microk8s kubectl create secret generic dmz-secrets \
                            --from-literal=DB_HOST="$DB_HOST" \
                            --from-literal=DB_USER="$DB_USER" \
                            --from-literal=DB_PASSWORD="$DB_PASSWORD" \
                            --from-literal=DB_TABLE="$DB_TABLE"
                        
                        microk8s kubectl delete -f DevSecOps/kubernetes/dmz_service.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/dmz_deployment.yaml --ignore-not-found

                        microk8s kubectl apply -f DevSecOps/kubernetes/dmz_service.yaml
                        microk8s kubectl apply -f DevSecOps/kubernetes/dmz_deployment.yaml
                        '''
                    }
                }
            }
        }
    }

    post {
        always {
            sh 'rm -rf DevSecOps'
            sh 'docker logout'
        }
    }
}
