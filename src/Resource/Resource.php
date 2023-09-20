<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace Chameleon2die4\WPBonesExtend\Resource;

use Chameleon2die4\WPBonesExtend\Collection;
use Chameleon2die4\WPBonesExtend\Contracts\Arrayable;
//use Chameleon2die4\WPBonesExtend\Model\Model;
use WPKirk\WPBones\Database\Support\Collection as CoreCollection;
use Chameleon2die4\WPBonesExtend\Traits\CallStatic;
use WPKirk\WPBones\Database\Support\Model as CoreModel;

class Resource
{

    use CallStatic;

    /**
     * @var array|Collection|null
     */
    private $resource;

    /**
     * @var mixed
     */
    protected $item;

    public function __construct($item = null)
    {
        if ($item) {
            $this->item = $item;
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @param \WP_REST_Request|null $request
     * @return array
     * @noinspection PhpUnusedParameterInspection
     */
    public function toArray(\WP_REST_Request $request = null)
    {
        if (is_null($this->resource)) {
            return [];
        }

        if ($this->item) {
            if ($this->item instanceof Arrayable) {
                return $this->item->toArray();
            } elseif ($this->item instanceof CoreModel) {
                return json_decode((string) $this->item, true);
            }

            return $this->item;
        }

        return is_array($this->resource)
          ? $this->resource
          : $this->resource->toArray();
    }

    /**
     * @param Collection|CoreCollection|array $items
     * @param \WP_REST_Request|null $request
     * @return Collection
     * @noinspection PhpMissingParamTypeInspection
     */
    protected function collection($items, \WP_REST_Request $request = null)
    {
        $this->setResource($items);

        return $this->resource->map(function ($item) use ($request) {
            $this->item = $item;

            return $this->toArray($request);
        });

//        return $this->resource->toArray($request);
    }

    /**
     * @param array|Collection|CoreCollection|null $resource
     * @noinspection PhpMissingParamTypeInspection
     */
    private function setResource($resource): void
    {
        if (is_array($resource)) {
            $this->resource = collect($resource);
        } elseif ($resource instanceof Collection) {
            $this->resource = $resource;
        } elseif ($resource instanceof CoreCollection) {
            $models = $resource->getArrayCopy();
//            $collection = collect();
//
//            foreach ($models as $model) {
//                $data = json_decode((string) $model, true);
//                $collection->push(new Model($data));
//            }

//            $this->resource = $collection;
            $this->resource = collect($models);
        } else {
            $this->resource = collect([$resource]);
        }
    }

}
