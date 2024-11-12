pipeline {
    agent any
    
    environment {
        SNYK_TOKEN= credentials('SNYK')
        DOCKER_HUB = credentials('DOCKER_HUB')
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
                        microk8s kubectl delete -f DevSecOps/kubernetes/db_pv.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/db_secrets.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/db_service.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/db_stateful_set.yaml --ignore-not-found

                        microk8s kubectl apply -f DevSecOps/kubernetes/db_pv.yaml
                        microk8s kubectl apply -f DevSecOps/kubernetes/db_secrets.yaml
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
                        microk8s kubectl delete -f DevSecOps/kubernetes/dmz_service.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/dmz_secrets.yaml --ignore-not-found
                        microk8s kubectl delete -f DevSecOps/kubernetes/dmz_deployment.yaml --ignore-not-found

                        microk8s kubectl apply -f DevSecOps/kubernetes/dmz_secrets.yaml
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
