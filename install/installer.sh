#!/bin/bash

#sudo apt-get update
#sudo apt-get install php5-mysql mysql-server nginx git
rm -rf cloud-web-v2
#部署前台模板
eval $(ssh-agent -s)
ssh-add id_rsa

git clone git@192.168.1.5:cloud-web-v2

cd cloud-web-v2

cd build

git clone https://github.com/jin123456bat/Framework3.0.git php

cd php

#删除不需要上传的文件
rm -rf ./.git
rm -rf ./.settings
rm -f ./.buildpath
rm -f ./.gitignore
rm -f ./.project
rm -f ./README

cd ..

#fxdata@2222CDS
#scp -v -r ./* root@192.168.1.225:/home/nginx/cm2
scp -v -r ./* root@192.168.2.73:/var/www/html

cd ..

cd ..

rm -rf cloud-web-v2
