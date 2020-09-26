var global={
    title:'管理后台',//页面标题
    user:{},//登录用户的信息
    params:{},//参数保存
    tableName:'PAY419',
    tokenName:'token',
    pageTabs: false, //是否开启页面选项卡功能，不推荐开启
    entry: ['Default','default'], //默认视图
    appurl:'./ht.php?',
    screenType:typeof(getScreen)=='function'?getScreen():'',
    theme: {//主题配置
        color: [
            {main: '#20222A',selected: '#009688',alias: 'default'},
            {main: '#03152A',selected: '#3B91FF',alias: 'dark-blue'},
            {main: '#2E241B',selected: '#A48566',alias: 'coffee'},
            {main: '#50314F',selected: '#7A4D7B',alias: 'purple-red'},
            {main: '#344058',logo: '#1E9FFF',selected: '#1E9FFF',alias: 'ocean'},
            {main: '#3A3D49',logo: '#2F9688',selected: '#5FB878',alias: 'green'},
            {main: '#20222A',logo: '#F78400',selected: '#F78400',alias: 'red'},
            {main: '#28333E',logo: '#AA3130',selected: '#AA3130',alias: 'fashion-red'},
            {main: '#24262F',logo: '#3A3D49',selected: '#009688',alias: 'classic-black'},
            {logo: '#226A62',header: '#2F9688',alias: 'green-header'},
            {main: '#344058',logo: '#0085E8',selected: '#1E9FFF',header: '#1E9FFF',alias: 'ocean-header'},
            {header: '#393D49',alias: 'classic-black-header'},
            {main: '#50314F',logo: '#50314F',selected: '#7A4D7B',header: '#50314F',alias: 'purple-red-header'},
            {main: '#28333E',logo: '#28333E',selected: '#AA3130',header: '#AA3130',alias: 'fashion-red-header'},
            {main: '#28333E',logo: '#009688',selected: '#009688',header: '#009688',alias: 'green-header'}
        ],
        initColorIndex: 0
    }
};