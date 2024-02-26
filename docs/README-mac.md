# 環境構築

## このドキュメントを見てわかること

TechTrain Railwayの問題を解くためにDockerとVisual Studio CodeとGitが必要です。  
そのため、自身のPC環境にDockerとVisual Studio CodeとGitのインストールをします。  
Visual Studio CodeにTechTrain Railwayのクリア条件を判定するツールをインストールします。  
次に、GitHubのリポジトリをフォークし、自身のPC環境にコードをダウンロードします。  
最後に、Dockerに関するコマンドを実行し、環境構築を行います。

### 環境構築について

1. Docker Desktopのインストール
    自身のMacに搭載されているCPUを確認し、[Install Docker Desktop on Mac](https://docs.docker.com/desktop/install/mac-install/) からDocker Desktopをダウンロードし、インストールします。  
    Docker Desktopをインストールした後、一度PCを再起動してからDocker Desktopを起動してください。  
    これにより、Dockerが正しく動作するか確認できます。
2. Visual Studio Codeのインストール  
    [Visual Studio Code](https://code.visualstudio.com/) から自分のOSに適したVisual Studio Codeをダウンロードする。
3. Visual Studio CodeにTechTrain Railwayのクリア条件を判定するツールをインストール  
    Visual Studio Codeを開き、拡張機能（Extensions）から「TechTrain Railway」という拡張機能を検索してインストールします。これにより、Railwayのクリア条件を簡単に判定できるようになります。
    ![TechTrain Railwayの拡張機能をインストール](./images/install-extensions.gif)
4. GitHubリポジトリのフォークとダウンロード
    1. GitHubリポジトリのフォーク
        [TechBowl-japan/laravel-stations-2 | GitHub](https://github.com/TechBowl-japan/laravel-stations-2) にアクセスし、右上の"Fork"ボタンをクリックして、リポジトリを自分のGitHubアカウントにフォークします。
        ![GitHubリポジトリのフォーク](./images/fork-repository.gif)
    2. Gitのインストール  
        GitHubからリポジトリをクローンするためには、Gitが必要です。  
        インストールされていない場合は、[Gitの公式サイト](https://git-scm.com/download/mac) で提示された選択肢から1つ選び、ダウンロードします。
    3. GitHubリポジトリのダウンロード
        フォークが完了したら、自分のGitHubアカウント上でフォークされたリポジトリを選択し、"Code"ボタンをクリックして、リポジトリのURLをコピーします。そして、ターミナルを開いて以下のコマンドを実行してリポジトリをダウンロードします。
        ```bash
        git clone https://github.com/{{あなたのGitHubID}}/laravel-stations-2.git
        ```
5. Visual Studio Codeでダウンロードしたリポジトリを開く
    ターミナルでリポジトリをダウンロードしたら、Visual Studio Codeを起動し、ファイル -> フォルダを開くを選択して、ダウンロードしたリポジトリのディレクトリを選択します。
6. Visual Studio Codeからターミナルを起動し環境構築する  
    左上のターミナル -> 新しいターミナルを選択して、ターミナルを起動します。  
    `cp .env.example .env` を実行し、必要な環境の情報が書かれた `.env` ファイルを作成します。  
    `docker compose build --no-cache` を実行します。  
    ※ Dockerコンテナのビルドおよび起動には時間がかかる場合があります。コマンドが正常に完了するまで待ってください。
7. Dockerコマンドでコンテナを起動  
    ターミナルでリポジトリのディレクトリに移動し、以下のコマンドを実行してDockerコンテナを起動します。
    ```bash
    docker compose up -d
    ```
    ※ Dockerコンテナのビルドおよび起動には時間がかかる場合があります。コマンドが正常に完了するまで待ってください。
8. Dockerコマンドでコンテナを起動を確認  
    手順7.で起動したDockerコンテナのプロセスについて確認をします。  
    `docker compose ps` コマンドを実行してプロセスか確認してください。
    ※ Dockerが使用するポートが他のアプリケーションと競合していないか確認してください。
9. Laravelに必要なライブラリをインストール  
    `docker compose exec php-contianer composer install` を実行し、ライブラリをインストールします。
10. .env ファイル内の `APP_KEY=` の右辺が空白の場合、`docker compose exec php-container php artisan key:generate` を実行します。
11. .env ファイル内の `APP_KEY=` にキーが登録されたことを確認し、`docker compose up -d` を実行します。
12. [http://localhost:8888](http://localhost:8888) にアクセスする。
13. 環境構築完了後の確認  
    環境構築が正常に終了したことを確認するために、Visual Studio Codeでリポジトリを開いてから、ファイルの変更や追加ができるか確認してください。  
    また、TechTrain Railwayの拡張機能が正しく機能しているかも確認してください。

以上で問題解決のための環境が整いました。  
Visual Studio Codeを使用してコードを編集し、「TechTrain Railway」という拡張機能から「できた!」と書かれた青いボタンをクリックすると判定が始まります。
