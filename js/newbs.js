function sync(method,url,data,done,fail)
{
    var methodMap = {
        "update":"put",
        "new":"post",
        "create":"post",
        "delete":"delete",
        "remove":"delete",
        "get":"get",
        "list":"get"
    };
    $.ajax(
        {
            url:url,
            method: methodMap[method],
            data: JSON.stringify(data),
            done: done,
            fail: fail
        }
    );
}

/**
 *
 * @param data
 * @param options
 * - type
 * - collection
 * - view
 * - url
 * - autofetch
 * - parser
 */
function item(data,options)
{
    var isNew = false;
    // invalid options provided
    if(!options || options.constructor===Object)
        return null;
    // not allowed not to provide a type
    if(!options.type)
        return null;
    // neither data nor URL to fetch data was provided => return null
    if(!data || !options.hasOwnProperty("url"))
        return null;

    var obj = {
        id: data && data.id ? data.id : null,
        type: data.type ? data.type : options.type,
        attributes: data.attributes ? data.attributes : {},
        prevAttributes: data.attributes?data.attributes: {},
        changedAttributes: [],
        collection: options.collection?options.collection:null,
        view: options.view?options.view:null,
        url: options.url?options.url:null,
        onCreate: options.onCreate && options.onCreate.constructor===Function?options.onCreate:function () {},
        onCreateFail: options.onCreateFail && options.onCreateFail.constructor===Function?options.onCreateFail:function () {},
        onUpdate: options.onUpdate && options.onUpdate.constructor===Function?options.onUpdate:function () {},
        onUpdateFail: options.onUpdateFail && options.onUpdateFail.constructor===Function?options.onUpdateFail:function () {},
        onFetch: options.onFetch && options.onFetch.constructor===Function?options.onFetch:function () {},
        onFetchFail: options.onFetchFail && options.onFetchFail.constructor===Function?options.onFetchFail:function () {},
        onRemove: options.onRemove && options.onRemove.constructor===Function?options.onRemove:function () {},
        onRemoveFail: options.onRemoveFail && options.onRemoveFail.constructor===Function?options.onRemoveFail:function () {},
    };

    /**
     * update object
     * @param p1
     * @param p2
     * @return {boolean}
     */
    obj.set = function (p1,p2)
    {
        if(!p1)
            return false;
        // in case an object was passed, do a bulk set
        if(p1.constructor===Object) {
            obj.prevAttributes = obj.attributes;
            obj.changedAttributes = [];
            for (var attr in obj.attributes) {
                obj.changedAttributes.push(attr);
                obj.attributes[attr] = p1.hasOwnProperty(attr)?p1[attr]:null;
            }
            return true;
        }

        if(p1.constructor!==String) {
            return false;
        }
        if(!attributes.hasOwnProperty(p1)) {
            return false;
        }

        // perform set
        obj.prevAttributes = obj.attributes;
        obj.changedAttributes.push(p1);
        attributes[p1] = p2;

        var url = getUrl();
        if(!url)
            return false;

        sync("put",url,obj.toJSON(),updateDone,updateFail);
    };

    function updateDone(data)
    {

    }

    function updateFail(xhr)
    {

    }

    /**
     * @return {boolean}
     */
    obj.remove = function()
    {
        var url = getUrl();
        if(!url)
            return false
        sync("remove",url,null,removeDone,removeFail);
        return true;
    };

    /**
     * callback to be called when remove ok
     */
    function removeDone()
    {
        if(obj.collection)
            obj.collection.ping("remove",obj);
        if(obj.view)
            obj.view.remove();
        obj.onRemove(obj.toJSON());

        delete obj;
    }

    /**
     * callback to be called when remove failed
     */
    function removeFail(xhr)
    {
        obj.onRemoveFail(obj,xhr);
    }

    /**
     * fetch data from server
     * @return {boolean}
     */
    obj.fetch = function ()
    {
        var url = getUrl();
        if(!url)
            return false;

        sync("get",url,null,fetchDone,fetchFail);
        return true;
    };

    /**
     * callback to be called when a failed fetch was performed
     * @param xhr
     */
    function fetchFail(xhr)
    {
        if(isNew) {
            obj.onFetchFail(xhr);
            delete obj;
            return;
        }
        obj.attributes = obj.prevAttributes;
        obj.onFetchFail(obj,xhr);
    }

    /**
     * callback to be called when a successful fetch was performed
     * @param data
     */
    function fetchDone(data)
    {
        parseResponse(data);
        if(obj.collection)
            obj.collection.ping("update",obj);
        if(obj.view)
            obj.view.render();
        obj.onFetch(obj);
    }

    /**
     * Finished
     * default parser function to parse response from server;
     * to be used with fetch, update & create
     * @param data
     */
    function defaultParser(data)
    {
        obj.id = data.data.id;
        obj.type = data.data.type;

        // populates attributes
        for(var attr in data.data.attributes) {
            if(data.data.attributes[attr] && data.data.attributes[attr].constructor===Object)
                obj.attributes[attr] = getIncludedData(data.data.attributes[attr],data);
            else
                obj.attributes[attr] = data.data.attributes;
        }

        /**
         * searched inside a included member of a JSON API response for the
         * @param subject
         * @param data
         * @return {null}
         */
        function getIncludedData(subject,data)
        {
            if(!subject.id || !subject.type) {
                dbg && console.log("invalid subject structure")
                return null;
            }
            if(!data.includes || data.includes.constructor!==Array) {
                dbg && console.log("no includes present or invalid")
                return null;
            }

            // iterates through includes memebers
            data.includes.forEach(function (incData) {
                if(incData.type==subject.type && incData.id==subject.id) {
                    incOptions = {};
                    if(incData.links && incData.links.self)
                        incOptions.url = incData.links.self;
                    // TODO: parse relationships
                    return item(incData,incOptions);
                }
            });
        }
    }
    var parseResponse = options.parser && options.parser.constructor===Function? options.parse: defaultParser;
    /**
     * Finished
     * generate a JSON object to be used with the sever
     */
    obj.toJSON = function ()
    {
        var json = {data:{type:this.type,attributes:{}}};
        if(this.id) json.data.id = this.id;
        for(var attr in attributes) {
            if (attributes[attr] && attributes[attr].constructor === Object)
                data.attributes[attr] = attributes[attr].id ? attributes[attr].id : null;
            else
                data.attributes[attr] = attributes[attr];
        }
        return json;
    };

    /**
     * Finished
     * Return URL to use for talking to server.
     * First choice is to return object URL
     * 1st fallback is to return collection URL/id
     * last fallback return null
     * @return string|null
     */
    function getUrl()
    {
        if(obj.url)
            return url;

        if(obj.collection && obj.collection.url)
            return obj.collection.url+"/"+obj.id;

        return null;
    }


    if(!data && options.autofetch) {
        isNew = true;
        obj.fetch();
    }
    return obj;
}

function collection() {

}

function itemView(container,) {

}

function collectionView() {

}